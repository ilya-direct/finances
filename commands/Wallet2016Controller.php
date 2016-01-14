<?php

namespace app\commands;
use \Dropbox as dbx;
use Yii;
use app\models\WalletDB as DB;
use yii\helpers\ArrayHelper;

class Wallet2016Controller extends \yii\console\Controller
{
    public function actionIndex($nox="Stranger")
    {
        print "Hello ".$nox;
    }

    public function actionDbxdownload()
    {
        $client=new  dbx\Client(\Yii::$app->params['dbx_token'],'directapp','UTF-8');

        $download_path=Yii::getAlias('@finance_download_path');


        if(!is_dir($download_path) and !\yii\helpers\FileHelper::createDirectory($download_path,0777,true))
            throw new \Exception('can\'t open download directory');


        $finances=$client->getMetadataWithChildren('/finances')['contents'];
        $recs=DB\DbxFinance::find()->where('year=>2016')->orderBy(['year'=> SORT_ASC,'month' => SORT_ASC])->all();
        // --- функция поиска
        function search_info($pattern,$finances){
            foreach($finances as $fin){
                if(preg_match('/'.$pattern.'/',$fin['path'])){
                    return $fin;
                }
            }
            return false;
        };
        // ---
        foreach( $recs as $rec){
            $yearmonth=$rec->year.'.'.$rec->monthStr;
            //TODO: решить проблему с API и убрать функцию SEARCH_INFO
            $info=search_info($yearmonth.'xlsx',$finances);
            if(!$info) throw new \Exception('not found file '. $yearmonth);

            $download_filename=$download_path.DIRECTORY_SEPARATOR.$yearmonth.'.xlsm';
            $info['modified']=dbx\Client::parseDateTime($info['modified'])->format("Y-m-d H:i:s");
            if($info['modified']>$rec->modified_time || $rec->exists==0){
                $rec->download_time=date('Y-m-d H:i:s');
                $rec->modified_time=$info['modified'];
                $rec->file_name=$info['path'];
                $rec->exists=1;
                $rec->in_db=0;
                $rec->csv_converted=0;
                if(!is_null($client->getFile($info['path'],fopen($download_filename,'wb')))){
                    $rec->update();
                    echo "$yearmonth updated\n";
                }
            }
        }
    }


    public function actionParseXlsx(){
        $fins=DB\DbxFinance::find()
            ->where(['>=','year',2016])
            ->orderBy(['year'=> SORT_ASC,'month' => SORT_ASC,'downloaded'=>1])->all();
        $input_dir=Yii::getAlias('@finance_download_path');
        if(!is_dir($input_dir))
            throw new \Exception('can\'t find input directory');
        $zeroDayRow=8; // ряд  заголовков в файле
        $xlsx=new ExcelHelper();
        foreach($fins as $fin){
            $file_name=$fin->year.'.'.$fin->monthStr.'xlsx';
            $input_filepath=$input_dir.'/'.$file_name;
            if(!file_exists($input_filepath))
                throw new \Exception("file $input_filepath not found");

            $document = \PHPExcel_IOFactory::load($input_filepath);
            $document->setActiveSheetIndex(0);
            // Получаем активный лист
            $sheet = $document->getActiveSheet();
            $pColumns=DB\TransactionCategory::fileColumns();
            $xlsx->check_header($sheet,$pColumns); // проверка совпадения заголовков в файле с заданными
            $max_day=date('d',mktime(0,0,0,$fin->month+1,0,$fin->year));

            $date=new \DateTime();

            for($current_day=1;$current_day<=$max_day;$current_day++){
                $date->setDate($fin->year,$fin->month,$current_day);
                $date_cell=$sheet->getCell($xlsx->getDateLetter().$xlsx->dayRow($current_day));
                if($date_cell->getValue()!==$date->format('d.m.Y')){
                    throw new \Exception('Date '.$date.' is different from file date  '
                        .$date_cell->getValue().'in '.$input_filepath);
                }

                foreach($pColumns as $column){
                    $cell=$sheet->getCell($column->letter.$xlsx->dayRow($current_day));
                    $cellValue=$cell->getValue();
                    if(empty($cellValue)){
                        $xlsx->clearDb($date->format('Y-m-d'),$column);
                        continue;
                    }
                    switch($column->type){
                        case 'single':
                            DB\Record::insert_single($date->format('Y-m-d'),$column->name,$cellValue);
                            break;
                        case 'multiple':
                            //$desc_letter=chr(ord($cell->getColumn())+1);
                            //$desc=$sheet->getCell($desc_letter.($zeroDayRow+$current_day));
                            $descValue=$xlsx->nextCell($cell)->getValue();
                            DB\Record::insert_multiple($date->format('Y-m-d'),$column->name,$cellValue,$descValue);
                            break;
                        case 'note':
                            DB\Note::insert_note($date->format('Y-m-d'),$cellValue);
                            break;
                        case 'realmoney':
                            $realmoney=(int) $cellValue;
                            if (empty($realmoney)) continue;
                            $correction=$xlsx->nextCell($cell)->getValue();
                            $consider=$xlsx->prevCell($cell)->getValue();
                            DB\BalanceCheck::insert_checkpoint($date->format('Y-m-d'),$consider,$realmoney,$correction);
                            break;
                    }
                }
            }

            // ------------------
            $date->setDate($fin->year,$fin->month,$max_day);
            $correctionCell=$sheet->getCell($xlsx->correctionColumn($pColumns).$xlsx->dayRow($max_day));
            $correctionCellValue=$correctionCell->getValue();
            if(is_int($correctionCellValue)){
                DB\Record::setCorrection($date->format('Y-m-d'),$correctionCellValue);
                if(((int)$date->format('U'))<time()){

                    current($fin)==end($fin);
                    $date->modify('+1 day');
                    $this->actionGenerateMonthTmpl($date->format('Y'),$date->format('m'));
                }
            }elseif(empty($correctionCellValue)){
                DB\Record::deleteCorrection($date->format('Y-m-d'));
            }

            // -----------
        }

    }



    public function actionGenerateMonthTmpl($year='',$month=''){
        $year=empty((int) $year) ? date('Y') : (int) $year;
        $month=(int)(empty((int) $month) ? date('m') : $month);
        $monthstr=sprintf("%02d", $month);

        $file_path='/finances/'.$year.'.'.$monthstr.'.xlsx';
        $output=Yii::getAlias('@temp/'.$year.'.'.$monthstr.'.xlsx');
        $dbxClient=new  dbx\Client(\Yii::$app->params['dbx_token'],'directapp','UTF-8');
        if(!is_null($dbxClient->getFile($file_path,fopen($output.'.tmp','w+')))) return;

        $date = new \DateTime("$year-$month-1");
        $date->sub(new \DateInterval('P1D'));

        $bc=DB\BalanceCheck::findOne(['date'=> $date->format('Y-m-d')]);
        $start_sum=is_object($bc) ? $bc->realmoney : $start_sum="";
        unset($bc);

        $path=Yii::getAlias('@templates/month_template_2016.xlsx');
        // Открываем файл
        $xlsx = \PHPExcel_IOFactory::load($path);
        // Устанавливаем индекс активного листа
        $xlsx->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $xlsx->getActiveSheet();
        // Вставляем месяц и год в ячейку D4
        $months= [1=>'Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];

        $sheet->setCellValue('D4',$months[$month].' '.$year);
        // Вставляем Начальную сумму в ячейку F5
        $sheet->setCellValue('F5',$start_sum);

        // максимальный день месяца
        $maxday=date('d',mktime(0,0,0,$month+1,0,$year));
        $days=['вс','пн','вт','ср','чт','пт','сб'];
        $start_weekday=date('w',strtotime("$year-$month-1"));
        for($day=1; $day<=$maxday; $day++){
            $pRow=8+$day;
            // Запись даты с D9
            $sheet->setCellValueByColumnAndRow(3,$pRow, sprintf("%02d", $day).'.'.$monthstr.'.'.$year);
            $weekday=($start_weekday+ $day-1) % 7;
            // Запись дня недели в ячейку E9
            $sheet->setCellValueByColumnAndRow(4,$pRow, $days[$weekday]);
            if($weekday==0)
                //жирная нижняя граница
                $xlsx->getActiveSheet()->getStyle('D'.$pRow.':T'.$pRow)
                    ->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_MEDIUM);
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($xlsx, 'Excel2007');;
        // Сохранение
        //$output=Yii::getAlias('@temp/'.$year.'.'.$monthstr.'.xlsx');
        //$file_path='/finances/'.$year.'.'.$monthstr.'.xlsx';

        $dbxClient=new  dbx\Client(\Yii::$app->params['dbx_token'],'directapp','UTF-8');
        $objWriter->save($output);

        if(is_null($dbxClient->getFile($file_path,fopen($output.'.tmp','w+')))) {
            $result = $dbxClient->uploadFile($file_path, dbx\WriteMode::add(), fopen($output,'rb'));
        }
    }
}
