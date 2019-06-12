<?php

namespace app\commands;

use integrations\dropbox\DropboxApi;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use app\commands\models\helpers\ExcelHelper;
use app\models\Finance2016 as DB;
use DateTime;

class Wallet2019Controller extends Controller
{
    
    private function findFileInfo($dbxEntries, $fileName)
    {
        foreach ($dbxEntries as $entry) {
            if ($entry['name'] === $fileName) {
                return $entry;
            }
        }
        
        return false;
    }

    public function actionDbxDownload()
    {
        Console::output('Downloading files from dropbox ...');
        $downloadPath = Yii::getAlias('@finance_download_path');

        if (!is_dir($downloadPath) and !mkdir($downloadPath, 0777, true)) {
            throw new Exception('Cannot create downloading directory');
        }

        /** @var DropboxApi $dropbox */
        $dropbox = Yii::$app->dropbox;
        
        $finances = $dropbox->filesListFolder('/finances');

        /** @var DB\DbxFinance[] $recs */
        $recs = DB\DbxFinance::find()
            ->where(['>=', 'year', 2019])
            ->andWhere(['>=', 'month', 6])
            ->orderBy([
                'year' => SORT_ASC,
                'month' => SORT_ASC,
            ])->all();

        foreach ($recs as $rec) {
            $yearMonth = $rec->year . '.' . $rec->monthStr;
            $info = $this->findFileInfo($finances, $yearMonth . '.xlsm');

            if (!$info) {
                throw new Exception('Cannot found file ' . $yearMonth . '.xlsm in dropbox');
            }

            $downloadFilePath = $downloadPath . '/' . $yearMonth . '.xlsm';
            $info['modified'] = $dropbox->parseDateTime($info['server_modified']);
            if (($info['modified'] > $rec->modified_time) || $rec->downloaded == 0) {
                $action = $rec->downloaded ? 'updated' : 'downloaded';
                $rec->downloaded_time = date('Y-m-d H:i:s');
                $rec->modified_time = $info['modified'];
                $rec->file_name = $info['path_lower'];
                $rec->downloaded = 1;
                $rec->in_db = 0;
                if ($dropbox->filesDownload($info['path_lower'], $downloadFilePath)) {
                    $rec->update();
                    Console::output($yearMonth . ' ' . $action . ' from dropbox');
                } else {
                    Console::output($yearMonth . ' NOT ' . $action . ' from dropbox');
                }
            }
        }
    }


    public function actionParseXlsx()
    {
        Console::output('Parsing downloaded files ...');
        /** @var DB\DbxFinance[] $fins */
        $fins = DB\DbxFinance::find()
            ->where(['>=', 'year', 2019])
            ->where(['>=', 'month', 6])
            ->andWhere(['downloaded' => true, 'in_db' => false])
            ->orderBy([
                'year' => SORT_ASC,
                'month' => SORT_ASC,
            ])->all();
        $inputDir = Yii::getAlias('@finance_download_path');
        if (!is_dir($inputDir)) {
            throw new Exception('Cannot find input directory');
        }

        $dateColumn = DB\Column::getDateLetter(); //  колонка с датой
        $zeroDayRow = Yii::$app->params['headerRow']; // ряд  заголовков в файле

        foreach ($fins as $fin) {
            $fileName = $fin->year . '.' . $fin->monthStr . '.xlsm';
            $inputFilePath = $inputDir . '/' . $fileName;
            if (!file_exists($inputFilePath)) {
                throw new Exception('File ' . $inputFilePath . ' not found');
            }
            $document = \PHPExcel_IOFactory::load($inputFilePath);
            $document->setActiveSheetIndex(0);
            // Получаем активный лист
            $sheet = $document->getActiveSheet();
            $xlsx = new ExcelHelper($sheet);

            /** @var DB\Column[] $headers */
            $headers = DB\Column::find()
                ->where(['and', ['not', ['letter' => null]], ['deleted' => 0]])
                ->orderBy([
                    'letter' => SORT_ASC
                ])
                ->all();

            $xlsx->checkHeaders($headers); // проверка совпадения заголовков в файле с заданными
            // TODO: Заполнение начальной суммы
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
            $fin->save(false);
            Console::output($fin->year . '.' . $fin->monthStr . ' imported to db');

        }

    }

    public function actionBalanceCheck()
    {
        Console::output('Checking balance ...');
        /** @var DB\BalanceCheck[] $points */
        $points = DB\BalanceCheck::find()->orderBy(['date' => SORT_ASC])->all();
        /** @var DB\BalanceCheck $pointStart */
        $pointStart = array_shift($points);
        $totalSum = $pointStart->real;
        foreach ($points as $pointEnd) {
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
                    . '. Must be: ' . $mustBe);
            }
            $pointStart = $pointEnd;
        }
        /** @var DB\BalanceCheck $lastPoint */
        $lastPoint = end($points);
        Console::output($lastPoint
            ? ('Last check-point at: ' . $lastPoint->date  . '. Real Balance: ' . $lastPoint->real)
            : 'no check-points found'
        );
        Console::output('Balance ok');
    }


    public function actionGenerateMonthTmpl($year = 0, $month = 0)
    {        
        $year = (int)($year ?: date('Y'));
        $month = (int)($month ?: date('m'));
        if ($year < 2019 && $month < 6) {

            Console::stdout('Generating can be done for year >= 2019, month >= 6 ');
            return;
        }
        Console::stdout('Generating ' . sprintf('%02d',$month) . '.' . $year . '.xlsm... ');
        $monthStr = sprintf("%02d", $month);
        $filePath = '/finances/' . $year . '.' . $monthStr . '.xlsm';
        $output = Yii::getAlias('@temp/' . $year . '.' . $monthStr . '.xlsm');
        
        /** @var DropboxApi $dropbox */
        $dropbox = Yii::$app->dropbox;
        if ($dropbox->filesSearch('/finances', $year . '.' . $monthStr . '.xlsm')) {
            Console::output('skipped! Already Exists!');
            return;
        }

        $path = Yii::getAlias('@templates/month_template_2019.xlsm');
        // Открываем файл шаблона
        $xlsx = \PHPExcel_IOFactory::load($path);
        // Устанавливаем индекс активного листа
        $xlsx->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $xlsx->getActiveSheet();
        // Вставляем месяц и год в ячейку D4
        $months = [
            1 => 'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        $sheet->setCellValue('D4', $months[$month] . ' ' . $year);
        
        // TODO: copy the initial balance (start balance)

        // максимальный день месяца
        $maxDay = date('d', mktime(0, 0, 0, $month + 1, 0, $year));
        $days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
        $startWeekday = date('w', strtotime("$year-$month-1"));
        for ($day = 1; $day <= $maxDay; $day++) {
            $pRow = 8 + $day;
            // Запись даты с D9
            $dateValue = \PHPExcel_Shared_Date::FormattedPHPToExcel($year, $month, $day);
            $sheet->setCellValueByColumnAndRow(3, $pRow, $dateValue);
            // Запись дня недели в ячейку E9
            $weekday = ($startWeekday + $day - 1) % 7;
            $sheet->setCellValueByColumnAndRow(4, $pRow, $days[$weekday]);
            // Жирная нижняя граница, если конец недели
            if ($weekday == 0) {
                //жирная нижняя граница
                $xlsx->getActiveSheet()
                    ->getStyle('D' . $pRow . ':O' . $pRow)
                    ->applyFromArray([
                            'borders' => [
                                'bottom' => [
                                    'style' => \PHPExcel_Style_Border::BORDER_MEDIUM
                                ]
                            ]
                    ]);
            }
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($xlsx, 'Excel2007');
        $objWriter->save($output);
        
        $dropbox->filesUpload($filePath, $output);
        Console::output('ok!');
    }

    public function actionGenerateDbxFinanceTbl()
    {
        $startYear = 2019;
        $startMonth = 6;
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        for ($i = $startYear; $i <= $currentYear; $i++) {
            $maxMonth = ($i == $currentYear) ? $currentMonth : 12;
            for ($j = ($i == $startYear) ? $startMonth : 1; $j <= $maxMonth; $j++) {
                if (!DB\DbxFinance::find()->where(['year' => $i, 'month' => $j])->exists()) {
                    $dbx = new DB\DbxFinance(['year' => $i, 'month' => $j, 'downloaded' => 0]);
                    $this->actionGenerateMonthTmpl($i, $j);
                    $dbx->save();
                    Console::output('New month added : ' . $i . '.' . sprintf('%02d', $j));
                }
            }
        }
    }

    public function actionPerDay()
    {
        $this->actionGenerateDbxFinanceTbl();
        $this->actionDbxDownload();
        $this->actionParseXlsx();
        $this->actionBalanceCheck();
    }
}
