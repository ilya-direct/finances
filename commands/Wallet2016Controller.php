<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use app\commands\models\helpers\ExcelHelper;
use app\models\Finance2016 as DB;
use Dropbox as dbx;
use DateTime;

class Wallet2016Controller extends Controller
{
    public function actionIndex($nox = "Stranger")
    {
        print "Hello " . $nox;
    }

    public function actionDbxDownload()
    {
        $client = new  dbx\Client(Yii::$app->params['dbx_token'], 'directapp', 'UTF-8');
        $downloadPath = Yii::getAlias('@finance_download_path');
        if (!is_dir($downloadPath) and !mkdir($downloadPath, 0777, true)) {
            throw new \Exception('can\'t open download directory');
        }

        $finances = $client->getMetadataWithChildren('/finances')['contents'];
        $files = [];
        foreach ($finances as $fin) {
            $files[basename($fin['path'], '.xlsm')] = $fin;
        }

        /** @var DB\DbxFinance[] $recs */
        $recs = DB\DbxFinance::find()
            ->where(['>=', 'year', 2016])
            ->orderBy([
                'year' => SORT_ASC,
                'month' => SORT_ASC,
            ])->all();

        foreach ($recs as $rec) {
            $yearMonth = $rec->year . '.' . $rec->monthStr;
            $info = isset($files[$yearMonth]) ? $files[$yearMonth] : false;

            if (!$info) {
                throw new Exception('not found file ' . $yearMonth);
            }

            $downloadFilePath = $downloadPath . '/' . $yearMonth . '.xlsm';
            $info['modified'] = dbx\Client::parseDateTime($info['modified'])->format("Y-m-d H:i:s");
            if (($info['modified'] > $rec->modified_time) || $rec->downloaded == 0) {
                $action = $rec->downloaded ? 'updated' : 'downloaded';
                $rec->downloaded_time = date('Y-m-d H:i:s');
                $rec->modified_time = $info['modified'];
                $rec->file_name = $info['path'];
                $rec->downloaded = 1;
                $rec->in_db = 0;
                if (!is_null($client->getFile($info['path'], fopen($downloadFilePath, 'wb')))) {
                    $rec->update();
                    Console::output($yearMonth . ' ' . $action);
                }
            }
        }
    }


    public function actionParseXlsx()
    {
        /** @var DB\DbxFinance[] $fins */
        $fins = DB\DbxFinance::find()
            ->where(['>=', 'year', 2016])
            ->andWhere(['downloaded' => true, 'in_db' => false])
            ->orderBy([
                'year' => SORT_ASC,
                'month' => SORT_ASC,
            ])->all();
        $inputDir = Yii::getAlias('@finance_download_path');
        if (!is_dir($inputDir)) {
            throw new Exception('can\'t find input directory');
        }

        $dateColumn = DB\Column::getDateLetter(); //  колонка с датой
        $zeroDayRow = Yii::$app->params['headerRow']; // ряд  заголовков в файле

        foreach ($fins as $fin) {
            $fileName = $fin->year . '.' . $fin->monthStr . '.xlsm';
            $inputFilePath = $inputDir . '/' . $fileName;
            if (!file_exists($inputFilePath)) {
                throw new \Exception("file $inputFilePath not found");
            }
            $document = \PHPExcel_IOFactory::load($inputFilePath);
            $document->setActiveSheetIndex(0);
            // Получаем активный лист
            $sheet = $document->getActiveSheet();
            $xlsx = new ExcelHelper($sheet);
            /** @var DB\Column[] $headers */
            $headers = DB\Column::find()
                ->where(['and', ['is not', 'letter', null], ['deleted' => 0]])
                ->all();
            $xlsx->checkHeaders($headers); // проверка совпадения заголовков в файле с заданными
            $maxDay = date('d', mktime(0, 0, 0, $fin->month + 1, 0, $fin->year)); // максимальный день месяца

            $date = new DateTime();
            for ($currentDay = 1; $currentDay <= $maxDay; $currentDay++) {
                $currentRow = $zeroDayRow + $currentDay;
                $date->setDate($fin->year, $fin->month, $currentDay);
                $dateValue = ExcelHelper::cellValue($xlsx->getCell($dateColumn, $currentRow));
                if ($dateValue !== $date->format('d.m.Y')) {
                    throw new Exception('Date ' . $date->format('d.m.Y')
                        . ' is different from file date  ' . $dateValue
                        . ' in ' . $fileName);
                }
                foreach ($headers as $header) {
                    $cell = $xlsx->getCell($header->letter, $currentRow);
                    $cellValue = ExcelHelper::cellValue($cell);
                    if (empty($cellValue)) { // пустая строка
                        DB\Record::clearDb($date->format('Y-m-d'), $header);
                        continue;
                    }
                    switch ($header->column_type_id) {
                        case DB\Column::TYPE_SINGLE:
                            DB\Record::insertSingle($date, $header, $cellValue);
                            break;
                        case DB\Column::TYPE_MULTIPLE:
                            $columnCell = $cell->getColumn();
                            $rowCell = $cell->getRow();
                            $descValue = ExcelHelper::cellValue($xlsx->nextCell($columnCell, $rowCell));
                            DB\Record::insertMultiple($date->format('Y-m-d'), $header, $cellValue, $descValue);
                            break;
                        case DB\Column::TYPE_NOTE:
                            DB\Note::insertNote($date->format('Y-m-d'), $cellValue);
                            break;
                        case DB\Column::TYPE_CHECKPOINT:
                            $realMoney = ExcelHelper::cellValue($cell);
                            $columnCell = $cell->getColumn();
                            $rowCell = $cell->getRow();
                            $correction = ExcelHelper::cellValue($xlsx->nextCell($columnCell, $rowCell));
                            $consider = ExcelHelper::cellValue($xlsx->prevCell($columnCell, $rowCell));
                            DB\BalanceCheck::insertCheckpoint($date->format('Y-m-d'), $consider, $realMoney, $correction);
                            break;
                        case DB\Column::TYPE_CORRECTING:
                        case DB\Column::TYPE_DATE:
                            break;
                        default:
                            throw new Exception('Undefined column');
                    }
                }
            }

            // ------------------
            $date->setDate($fin->year, $fin->month, $maxDay);
            $correctionCellValue = ExcelHelper::cellValue($sheet->getCell('Y' . ($zeroDayRow + $maxDay)));
            if (!empty($correctionCellValue) || is_int($correctionCellValue)) {
                DB\Record::setCorrection($date->format('Y-m-d'), $correctionCellValue);
            } else {
                DB\Record::deleteCorrection($date->format('Y-m-d'));
            }
            // -----------

            $fin->in_db = true;
            if ($fin->save()) {
                Console::output($fin->year . '.' . $fin->month . ' imported to db');
            } else {
                throw new Exception($fin->year . ' ' . $fin->month . ' cannot update dbxFinance record');
            }

        }

        /*
        if(((int)$date->format('U'))<time()){
            $date->modify('+1 day');
            $this->actionGenerateMonthTmpl($date->format('Y'),$date->format('m'));
        }
        */

    }

    public function actionBalanceCheck()
    {
        /** @var DB\BalanceCheck[] $points */
        $points = DB\BalanceCheck::find()->orderBy(['date' => SORT_ASC])->all();
        /** @var DB\BalanceCheck $pointStart */
        $pointStart = array_shift($points);
        $totalSum = $pointStart->real;
        foreach ($points as $pointEnd) {
            Console::output($pointEnd->date);
            $sum = DB\Record::find()
                ->select(['sum' => 'sum([[sum]])'])
                ->where(['>', 'date', $pointStart->date])
                ->andWhere(['<=', 'date', $pointEnd->date])
                ->scalar();
            $totalSum += $sum;
            list($y, $m, $d) = explode('-', $pointEnd->date);
            $maxDay = date('d', mktime(0, 0, 0, $m + 1, 0, $y));

            $mustBe = ($d == $maxDay) ? $pointEnd->consider + $pointEnd->difference : $pointEnd->consider;

            if ($totalSum != $mustBe) {
                throw new Exception($pointEnd->date
                    . ': Sum in calculation: ' . $totalSum
                    . 'Must be: ' . $mustBe . 'false');
            }
            $pointStart = $pointEnd;
        }
        Console::output('balance ok');
    }


    public function actionGenerateMonthTmpl($year = 0, $month = 0)
    {
        $year = (int)(empty((int)$year) ? date('Y') : $year);
        $month = (int)(empty((int) $month) ? date('m') : $month);
        $monthStr = sprintf("%02d", $month);
        Console::stdout('Generating ' . $month . '.' . $year . '.xlsm... ');
        $filePath = '/finances/' . $year . '.' . $monthStr . '.xlsm';
        $output = Yii::getAlias('@temp/' . $year . '.' . $monthStr . '.xlsm');
        $dbxClient = new  dbx\Client(Yii::$app->params['dbx_token'], 'directapp', 'UTF-8');
        if (!is_null($dbxClient->getFile($filePath, fopen($output . '.tmp', 'w+')))) {
            Console::output('canceled! Already Exists!');
            return;
        }
        $date = new DateTime();
        $date->setDate($year, $month, 1);
        $date->sub(new \DateInterval('P1D'));

        $startSum = DB\BalanceCheck::find()
            ->select('real')
            ->where(['date' => $date->format('Y-m-d')])
            ->scalar();

        $path = Yii::getAlias('@templates/month_template_2016.xlsm');
        // Открываем файл
        $xlsx = \PHPExcel_IOFactory::load($path);
        // Устанавливаем индекс активного листа
        $xlsx->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $xlsx->getActiveSheet();
        // Вставляем месяц и год в ячейку D4
        $months = [
            1 => 'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь',
        ];

        $sheet->setCellValue('D4', $months[$month] . ' ' . $year);
        // Вставляем Начальную сумму в ячейку F5
        $sheet->setCellValue('F5', $startSum);

        // максимальный день месяца
        $maxDay = date('d', mktime(0, 0, 0, $month + 1, 0, $year));
        $days = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
        $startWeekday = date('w', strtotime("$year-$month-1"));
        for ($day = 1; $day <= $maxDay; $day++) {
            $pRow = 8 + $day;
            // Запись даты с D9
            $sheet->setCellValueByColumnAndRow(3, $pRow, sprintf("%02d", $day) . '.' . $monthStr . '.' . $year);
            // Запись дня недели в ячейку E9
            $weekday = ($startWeekday + $day - 1) % 7;
            $sheet->setCellValueByColumnAndRow(4, $pRow, $days[$weekday]);
            if ($weekday == 0) {
                //жирная нижняя граница
                $xlsx->getActiveSheet()
                    ->getStyle('D' . $pRow . ':T' . $pRow)
                    ->getBorders()
                    ->getBottom()
                    ->setBorderStyle(\PHPExcel_Style_Border::BORDER_MEDIUM);
            }
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($xlsx, 'Excel2007');
        $objWriter->save($output);
        if (is_null($dbxClient->getFile($filePath, fopen($output . '.tmp', 'w+')))) {
            $dbxClient->uploadFile($filePath, dbx\WriteMode::add(), fopen($output, 'rb'));
            Console::output('ok!');
        }
    }

    public function actionGenerateDbxFinanceTbl()
    {
        $startYear = 2016;
        $startMonth = 1;
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        for ($i = $startYear; $i <= $currentYear; $i++) {
            $maxMonth = ($i == $currentYear) ? $currentMonth : 12;
            for ($j = ($i == $startYear) ? $startMonth : 1; $j <= $maxMonth; $j++) {
                if (!DB\DbxFinance::find()->where(['year' => $i, 'month' => $j])->exists()) {
                    $dbx = new DB\DbxFinance(['year' => $i, 'month' => $j, 'downloaded' => 0]);
                    $dbx->save();
                }
            }
        }
    }
}
