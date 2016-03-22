<?php

namespace app\commands;

use app\commands\models\helpers\ExcelHelper;
use \Dropbox as dbx;
use Yii;
use app\models\Finance2016 as DB;
use yii\helpers\Console;

class Wallet2016Controller extends \yii\console\Controller
{
    public function actionIndex($nox = "Stranger")
    {
        print "Hello " . $nox;
    }

    public function actionDbxDownload()
    {
        $client = new  dbx\Client(Yii::$app->params['dbx_token'], 'directapp', 'UTF-8');

        $downloadPath = Yii::getAlias('@finance_download_path');

        if (!is_dir($downloadPath) and !mkdir($downloadPath, 0777, true))
            throw new \Exception('can\'t open download directory');

        $finances = $client->getMetadataWithChildren('/finances')['contents'];
        $files = [];
        foreach ($finances as $fin) {
            $files[basename($fin['path'], ".xlsm")] = $fin;
        }

        $recs = DB\DbxFinance::find()->where(['>=', 'year', 2016])->orderBy(['year' => SORT_ASC, 'month' => SORT_ASC])->all();

        foreach ($recs as $rec) {
            $yearmonth = $rec->year . '.' . $rec->monthStr;
            $info = isset($files[$yearmonth]) ? $files[$yearmonth] : false;

            if (!$info)
                throw new \Exception('not found file ' . $yearmonth);

            $downloadFilePath = $downloadPath . '/' . $yearmonth . '.xlsm';
            $info['modified'] = dbx\Client::parseDateTime($info['modified'])->format("Y-m-d H:i:s");
            if ($info['modified'] > $rec->modified_time || $rec->downloaded == 0) {
                $rec->downloaded_time = date('Y-m-d H:i:s');
                $rec->modified_time = $info['modified'];
                $rec->file_name = $info['path'];
                $rec->downloaded = 1;
                $rec->in_db = 0;
                if (!is_null($client->getFile($info['path'], fopen($downloadFilePath, 'wb')))) {
                    $rec->update();
                    Console::output($yearmonth . ' updated');
                }
            }
        }
    }


    public function actionParseXlsx()
    {
        $fins = DB\DbxFinance::find()->where(['>=', 'year', 2016])->andWhere(['downloaded' => true, 'in_db' => false])->orderBy(['year' => SORT_ASC, 'month' => SORT_ASC])->all();
        $input_dir = Yii::getAlias('@finance_download_path');
        if (!is_dir($input_dir))
            throw new \Exception('can\'t find input directory');

        $dateColumn = DB\Column::getDateLetter(); //  колонка с датой
        $zeroDayRow = Yii::$app->params['headerRow']; // ряд  заголовков в файле

        foreach ($fins as $fin) {
            $file_name = $fin->year . '.' . $fin->monthStr . '.xlsm';
            $input_filepath = $input_dir . '/' . $file_name;
            if (!file_exists($input_filepath))
                throw new \Exception("file $input_filepath not found");

            $document = \PHPExcel_IOFactory::load($input_filepath);
            $document->setActiveSheetIndex(0);
            // Получаем активный лист
            $sheet = $document->getActiveSheet();
            $xlsx = new ExcelHelper($sheet);
            $pColumns = DB\Column::find()
                ->where(['and', ['is not', 'letter', null], ['deleted' => 0]])
                ->all();
            $xlsx->checkHeaders($pColumns); // проверка совпадения заголовков в файле с заданными
            $maxDay = date('d', mktime(0, 0, 0, $fin->month + 1, 0, $fin->year)); // максимальный день месяца

            $date = new \DateTime();
            for ($current_day = 1; $current_day <= $maxDay; $current_day++) {
                $currentRow = $zeroDayRow + $current_day;
                $date->setDate($fin->year, $fin->month, $current_day);
                $dateValue = ExcelHelper::cellValue($xlsx->getCell($dateColumn, $currentRow));

                if ($dateValue !== $date->format('d.m.Y')) {
                    throw new \Exception('Date ' . $date->format('d.m.Y') . ' is different from file date  ' . $dateValue . ' in ' . $input_filepath);
                }

                foreach ($pColumns as $column) {
                    if ($column->column_type_id == DB\Column::TYPE_DATE)
                        continue;
                    $cell = $xlsx->getCell($column->letter, $currentRow);
                    $cellValue = ExcelHelper::cellValue($cell);
                    if (empty($cellValue)) { // пустая строка
                        DB\Record::clearDb($date->format('Y-m-d'), $column);
                        continue;
                    }
                    switch ($column->column_type_id) {
                        case DB\Column::TYPE_SINGLE:
                            DB\Record::insertSingle($date, $column, $cellValue);
                            break;
                        case DB\Column::TYPE_MULTIPLE:
                            $columnCell = $cell->getColumn();
                            $rowCell = $cell->getRow();
                            $descValue = ExcelHelper::cellValue($xlsx->nextCell($columnCell, $rowCell));
                            DB\Record::insertMultiple($date->format('Y-m-d'), $column, $cellValue, $descValue);
                            break;
                        case DB\Column::TYPE_NOTE:
                            DB\Note::insertNote($date->format('Y-m-d'), $cellValue);
                            break;
                        case DB\Column::TYPE_CHECKPOINT:
                            $realmoney = ExcelHelper::cellValue($cell);
                            $columnCell = $cell->getColumn();
                            $rowCell = $cell->getRow();
                            $correction = ExcelHelper::cellValue($xlsx->nextCell($columnCell, $rowCell));
                            $consider = ExcelHelper::cellValue($xlsx->prevCell($columnCell, $rowCell));
                            DB\BalanceCheck::insertCheckpoint($date->format('Y-m-d'), $consider, $realmoney, $correction);
                            break;
                        case DB\Column::TYPE_CORRECTING:
                            break;
                        default:
                            throw new \Exception("Undefined column");
                            break;
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
                throw new \Exception($fin->year . ' ' . $fin->month . " cannot update dbxFinance record\n");
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
        $points = DB\BalanceCheck::find()->orderBy(['date' => SORT_ASC])->all();
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
            $maxday = date('d', mktime(0, 0, 0, $m + 1, 0, $y));

            $mustBe = ($d == $maxday) ? $pointEnd->consider + $pointEnd->difference : $pointEnd->consider;

            if ($totalSum != $mustBe) {
                throw new \Exception("{$pointEnd->date}: sum in calculation: {$totalSum} Must be: {$mustBe} false\n");
            }
            $pointStart = $pointEnd;

        }
        Console::output('balance ok');
    }


    public function actionGenerateMonthTmpl($year = '', $month = '')
    {
        $year = empty((int) $year) ? date('Y') : (int) $year;
        $month = (int) (empty((int) $month) ? date('m') : $month);
        $monthstr = sprintf("%02d", $month);

        $file_path = '/finances/' . $year . '.' . $monthstr . '.xlsx';
        $output = Yii::getAlias('@temp/' . $year . '.' . $monthstr . '.xlsx');
        $dbxClient = new  dbx\Client(Yii::$app->params['dbx_token'], 'directapp', 'UTF-8');
        if (!is_null($dbxClient->getFile($file_path, fopen($output . '.tmp', 'w+'))))
            return;

        $date = new \DateTime("$year-$month-1");
        $date->sub(new \DateInterval('P1D'));

        $bc = DB\BalanceCheck::findOne(['date' => $date->format('Y-m-d')]);
        $start_sum = is_object($bc) ? $bc->real : $start_sum = "";
        unset($bc);

        $path = Yii::getAlias('@templates/month_template_2016.xlsx');
        // Открываем файл
        $xlsx = \PHPExcel_IOFactory::load($path);
        // Устанавливаем индекс активного листа
        $xlsx->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $xlsx->getActiveSheet();
        // Вставляем месяц и год в ячейку D4
        $months = [1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

        $sheet->setCellValue('D4', $months[$month] . ' ' . $year);
        // Вставляем Начальную сумму в ячейку F5
        $sheet->setCellValue('F5', $start_sum);

        // максимальный день месяца
        $maxday = date('d', mktime(0, 0, 0, $month + 1, 0, $year));
        $days = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
        $start_weekday = date('w', strtotime("$year-$month-1"));
        for ($day = 1; $day <= $maxday; $day++) {
            $pRow = 8 + $day;
            // Запись даты с D9
            $sheet->setCellValueByColumnAndRow(3, $pRow, sprintf("%02d", $day) . '.' . $monthstr . '.' . $year);
            $weekday = ($start_weekday + $day - 1) % 7;
            // Запись дня недели в ячейку E9
            $sheet->setCellValueByColumnAndRow(4, $pRow, $days[$weekday]);
            if ($weekday == 0)
                //жирная нижняя граница
                $xlsx->getActiveSheet()->getStyle('D' . $pRow . ':T' . $pRow)->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_MEDIUM);
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($xlsx, 'Excel2007');;
        // Сохранение
        //$output=Yii::getAlias('@temp/'.$year.'.'.$monthstr.'.xlsx');
        //$file_path='/finances/'.$year.'.'.$monthstr.'.xlsx';

        $dbxClient = new  dbx\Client(Yii::$app->params['dbx_token'], 'directapp', 'UTF-8');
        $objWriter->save($output);

        if (is_null($dbxClient->getFile($file_path, fopen($output . '.tmp', 'w+')))) {
            $result = $dbxClient->uploadFile($file_path, dbx\WriteMode::add(), fopen($output, 'rb'));
        }
    }

    public function actionGenerateDbxFinanceTbl()
    {
        $startYear = 2016;
        $startMonth = 1;
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        for ($i = $startYear; $i <= $currentYear; $i++) {
            $j = ($i == $startYear) ? $startMonth : 1;
            $jmax = ($i == $currentYear) ? $currentMonth : 12;
            for (; $j <= $jmax; $j++) {
                if (!DB\DbxFinance::find()->where(['year' => $i, 'month' => $j])->exists()) {
                    $dbx = new DB\DbxFinance(['year' => $i, 'month' => $j, 'downloaded' => 0]);
                    $dbx->save();
                }
            }
        }
    }
}
