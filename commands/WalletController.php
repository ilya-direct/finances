<?php

namespace app\commands;
use \Dropbox as dbx;
use Yii;
use app\models\Finance2016 as DB;
use yii\helpers\Console;

class WalletController extends \yii\console\Controller
{
    public function actionIndex($nox)
    {
        $this->stderr("Creating migration history table ".$nox, Console::FG_YELLOW);
        //print $nox;
    }

    public function actionDbxdownload()
    {
        $client=new  dbx\Client(\Yii::$app->params['dbx_token'],'directapp','UTF-8');

        $download_path=Yii::getAlias('@finance_download_path');

        //TODO: Заменить is_dir функцией из библиотеки YII
        if(!is_dir($download_path) and !\yii\helpers\FileHelper::createDirectory($download_path,0777,true))
            throw new \Exception('can\'t open download directory');


        $finances=$client->getMetadataWithChildren('/finances')['contents'];
        $recs=DB\DbxFinance::find()
            ->where(['<=','year',2015])
            ->orderBy(['year'=> SORT_ASC,'month' => SORT_ASC])->all();
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
            $info=search_info($yearmonth,$finances);
            if(!$info) throw new \Exception('not found file '. $yearmonth);

            $download_filename=$download_path.DIRECTORY_SEPARATOR.$yearmonth.'.xlsm';
            $info['modified']=dbx\Client::parseDateTime($info['modified'])->format("Y-m-d H:i:s");
            if($info['modified']>$rec->modified_time || $rec->downloaded==0){
                $rec->downloaded_time=date('Y-m-d H:i:s');
                $rec->modified_time=$info['modified'];
                $rec->file_name=$info['path'];
                $rec->downloaded=1;
                $rec->in_db=0;
                $rec->csv_converted=0;
                if(!is_null($client->getFile($info['path'],fopen($download_filename,'wb')))){
                    $rec->update();
                    echo "$yearmonth updated\n";
                }
            }
        }
    }

    public function actionXlsm2csv(){

        $input_path=Yii::getAlias('@finance_download_path');
        if(!is_dir($input_path))
            throw new \Exception('can\'t find input directory');

        $output_path=Yii::getAlias('@finance_csv');
        if(!is_dir($output_path) and !\yii\helpers\FileHelper::createDirectory($output_path,0777,true))
            throw new \Exception('can\'t create output directory');


        $recs=DB\DbxFinance::find()
            ->where(['downloaded'=>1,'csv_converted'=>0])
            ->andWhere(['<=','year',2015])
            ->orderBy(['year'=> SORT_ASC,'month'=>SORT_ASC])->all();
        foreach($recs as $rec){
            $file_name=$rec->year.'.'.$rec->monthStr;
            $input_filepath=$input_path.'/'.$file_name.'.xlsm';
            if(file_exists($input_filepath)){
                $document = \PHPExcel_IOFactory::load($input_filepath);
                $document->setActiveSheetIndex(0);
                // Получаем активный лист
                $sheet = $document->getSheet(0);

                //  Get worksheet dimensions
                $highestRow = $sheet->getHighestRow();
                $highestColumn = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());

                $content='';

                //  Loop through each row of the worksheet in turn
                for ($row = 1; $row <= $highestRow; $row++){
                    //  Read a row of data into an array
                    $a=3;
                    for ($column = 0; $column < $highestColumn; $column++){
                        $cell = $sheet->getCellByColumnAndRow($column, $row);
                        $val=$val = $cell->getValue();
                        if(\PHPExcel_Shared_Date::isDateTime($cell)) {
                            $val = date('Y.m.d', \PHPExcel_Shared_Date::ExcelToPHP($val));
                        }
                        if((substr($val,0,1) === '=' ) && (strlen($val) > 1)){
                            $val = $cell->getOldCalculatedValue();
                        }
                        $content.=$val.';';
                    }
                    $content.="\r\n";
                }

                $output_filepath=$output_path.'/'.$file_name.'.csv';
                file_put_contents($output_filepath,$content);
                $rec->csv_converted=1;
                $rec->in_db=0;
                $rec->update();
                echo "$file_name converted\n";
            }else
                throw new \Exception("file $input_filepath not found\n");
        }
    }

    public function actionCsv2db(){
        function get_table_headers(&$handle,$needle){
            if (empty($needle)) return false;
            while(($data=fgetcsv($handle,null,';'))!==false){
                foreach($data as $cell){
                    //to_utf8($cell);
                    if ($cell===$needle){
                        return $data;
                    }
                }
            }
            return false;
        }

        function fix_headers(array &$headers){
            for($i=0; $i<count($headers); $i++){
                // TODO: оптимизировать (выборка одного поля из DB с возвращением строкой или null)
                $field_name_obj=(DB\Column::find()->select('name')->where(['value'=>$headers[$i]])->one());
                if(!is_null($field_name_obj)){
                    $field_name=$field_name_obj->name;
                    $field_array=explode('_',$field_name);
                    if(count($field_array)<=2){
                        $headers[$i]=$field_name;
                    }elseif(count($field_array)===3 && $field_array[2]=='multiple'){
                        $headers[$i]=$field_name;
                        if(!array_key_exists($i+1,$headers) || $headers[$i+1]!="")
                            throw new \Exception('Неверный формат колонок (формат заголовка таблицы)');
                        $headers[++$i]=$field_array[0].'_'.$field_array[1].'_'.'desc';
                    }
                    continue;
                }
                $add_th=[
                    'Дата' => 'date',
                    'ТС по расчету'=>'countmoney',
                    'ТС по деньгам'=>'realmoney',
                    'Разница'=>'difference'
                ];
                if(array_key_exists($headers[$i],$add_th)){
                    $headers[$i]=$add_th[$headers[$i]];
                    continue;
                }

                $headers[$i]=false;
            }

        }
        /*
        function to_utf8(&$str){
            $encoding=mb_detect_encoding($str);
            if($encoding=="UTF-8") return;
            iconv( $encoding,'UTF-8',$str);
        }
        */
        function merge_headers($h1,$h2){
            global $yearmonth;
            $n=(count($h1)>count($h2)) ? count($h1) : count($h2);
            $result=[];
            for($i=0; $i<$n ;++$i){
                if(empty($h1[$i])){
                    if(empty($h2[$i]))
                        $result[$i]=false;
                    else
                        $result[$i]=$h2[$i];
                }else{
                    if(empty($h2[$i]))
                        $result[$i]=$h1[$i];
                    else
                        throw new \Exception("Конфликт заголовков таблицы при слиянии $h1[$i] $h2[$i] $yearmonth");
                }
            }
            return $result;
        }
        // -- НАЧАЛО
        $input_path=Yii::getAlias('@finance_csv');
        if(!is_dir($input_path))
            throw new \Exception('can\'t find input directory in csv2db.php');

        $recs=DB\DbxFinance::find()
            ->where(['downloaded'=>1,'csv_converted'=>1,'in_db'=>0])
            ->andWhere(['<=','year',2015])
            ->orderBy(['year'=> SORT_ASC,'month'=> SORT_ASC])->all();

        foreach($recs as $rec){
            $yearmonth=$rec->year.'.'.$rec->monthStr;
            $input_filepath=$input_path.'/'.$yearmonth.'.csv';
            if(!file_exists($input_filepath))
                throw new \Exception("file $input_filepath not found");

            $file_handle=fopen($input_filepath,'r');
            if(!$file_handle)
                throw new \Exception("Не удалось открыть файл $input_filepath!");

            $headers_1=get_table_headers($file_handle,"ТС по расчету");
            if ($headers_1===false)
                throw new \Exception("Строка с корректировкой баланса не найдена $yearmonth");
            fix_headers($headers_1);

            $headers_2=get_table_headers($file_handle,"Дата");
            if ($headers_2===false)
                throw new \Exception("Строка с основными заголовком не найдена $yearmonth");
            fix_headers($headers_2);

            $headers=merge_headers($headers_1,$headers_2);
            unset($headers_1); unset($headers_2);

            $date_index=array_search('date',$headers);
            if ($date_index===false)
                throw new \Exception('В заголовках нет даты '.$yearmonth);

            $maxday=date('d',mktime(0,0,0,$rec->month+1,0,$rec->year));

            for($current_day=1;$current_day<=$maxday;$current_day++){
                $data=fgetcsv($file_handle,null,';');
                $date=$data[$date_index];
                if(!preg_match('/^([\d]{4})\.([\d]{2})\.([\d]{2})$/',$date,$matches) or
                    $matches[1]!=$rec->year || $matches[2]!=$rec->month || $matches[3]!=$current_day) {
                    throw new \Exception('Дата '.$date.' не совпадает с ожидаемой в файле '.$input_filepath);
                }
                //array_to_utf8($data);
                for($i=0;$i<count($headers);$i++){
                    if ($headers[$i]===false) continue;
                    $data[$i]=trim($data[$i]);
                    $header_parts=explode('_',$headers[$i]);
                    if (count($header_parts)==1){
                        if($headers[$i]=='realmoney'){
                            if(empty($data[$i])){
                                DB\BalanceCheck::deleteAll(['date'=>$date]);
                            }else{
                                $balance_check=DB\BalanceCheck::findOne(['date'=>$date]);
                                $balance_check=is_object($balance_check) ? $balance_check : new DB\BalanceCheck();
                                $balance_check->attributes=[
                                    'date'=>$date,
                                    'consider'=>$data[array_search('countmoney',$headers)],
                                    'real'=>$data[$i],
                                    'difference'=>$data[array_search('difference',$headers)]
                                ];
                                $balance_check->save();
                                unset($balance_check);
                            }

                            if($current_day==$maxday){
                                $correcting_id=DB\Item::getItemId("Корректировка");
                                if(empty($data[$i])){
                                    DB\Record::deleteAll(['date'=>$date,'itemid'=>$correcting_id]);
                                }else{
                                    DB\Record::insert_transaction_single($date,'correcting',$data[array_search('difference',$headers)],true);
                                }
                            }
                        }
                        continue;
                    }
                    if(empty($data[$i])){
                        $tcategories=DB\Column::find()->select('id')->where(['name'=>$headers[$i]])->column();
                        //TODO: сделать более безопасное удаление !!
                        if(!empty($tcategories))
                            DB\Record::deleteAll(['date'=>$date,'column_id'=>$tcategories]);
                        unset($tcategories);
                        continue;
                    }
                    if(count($header_parts)===2){
                        DB\Record::insert_transaction_single($date,$headers[$i],$data[$i]);
                    }elseif(count($header_parts)===3 && $header_parts[2]=='multiple'){
                        $coins=explode('|',$data[$i]);
                        $coins_desc=explode('|',$data[$i+1]);
                        if (count($coins)!=count($coins_desc)) {
                            throw new \Exception("Неверная запись {$date} : {$data[$i]} {$data[$i+1]} ");
                        }
                        $entries=[];
                        for($j=0;$j<count($coins);$j++){
                            $entry=new \stdClass();
                            $coins_desc[$j]=trim($coins_desc[$j]);
                            if(empty($coins_desc[$j]))
                                throw new \Exception("Нет описания $date {$data[$i]} {$data[$i+1]}");
                            $entry->sum=$coins[$j];
                            $entry->item=$coins_desc[$j];
                            $entries[]=clone $entry;
                        }
                        DB\Record::insert_transaction_multiple($date,$headers[$i],$entries);
                        unset($entry); unset($entries);
                    }
                }
            }
            /*
            $flags=[
                0b00001=>'Корректировка',
                0b00010=>'Всего получено',
                0b00100=>'Всего потрачено',
                0b01000=>'Стартовый капитал',
                0b10000=>'Конечный капитал'
            ];
            $total_flag= 0b00000;
            while(($data=fgetcsv($file_handle,null,';'))!==false){
                if(in_array($data[$date_index],$flags)){
                    $total_flag=$total_flag | array_search($data[$date_index],$flags);
                    $max_date="$rec->year.$rec->monthStr.$maxday";
                    if($data[$date_index]==$flags[0b00001] &&
                        // Корректировка записывается после того, как есть Запись в realmoney последнего дня месяца
                        DB\BalanceCheck::find()->where(['date'=>$max_date])->exists()
                    )
                        DB\Record::insert_transaction_single($max_date,'correcting',$data[$date_index+1],true);
                }
            }
            if (!($total_flag & 0b00001))
                throw new \Exception('Отсутствуют данные о корректировке '."$rec->year.$rec->monthStr.$maxday");
            */

            fclose($file_handle);
            echo "$yearmonth imported to db \n";
            //TODO: updateByPk подошло бы лучше
            DB\DbxFinance::updateAll(['in_db'=>1],['id'=>$rec->id]);
        }

        // Удаление названий транзакций, на которых нет ссылки в тбл record
        //TODO: оптимизировать
        $item_ids=DB\Record::find()->select('item_id')->distinct();
        if(!empty($item_ids))
            DB\Item::deleteAll(['not in','id',$item_ids]);
    }

    public function actionBalance_check(){

        function compare_values($actual,$mustbe,$date){
            if ($actual!=$mustbe)
                throw new \Exception("$date Sum in calculation: $actual Must be: {$mustbe} false\n");
        }
//        if(!DB\BalanceCheck::find()->where('year(date)=2013')->exists()) // for MySql
        if(!DB\BalanceCheck::find()->where('extract(year from [[date]])=2013')->exists())
            throw new \Exception('not initialized with script init');

        $points=DB\BalanceCheck::find()->orderBy('date')->all();
        $point_1=array_shift($points);
        $total_sum=$point_1->real;
        foreach($points as $point_2){
            $sum=DB\Record::find()->select(['sum(sum) as sum'])
                ->where(['>','date',$point_1->date])
                ->andWhere(['<=','date',$point_2->date])->one();
            $total_sum+=$sum->sum;
            list($y,$m,$d)=explode('-',$point_2->date);
            $maxday=date('d',mktime(0,0,0,$m+1,0,$y));
            if ($d==$maxday){
                compare_values($total_sum,$point_2->consider+$point_2->difference,$point_2->date);
            }else
                compare_values($total_sum,$point_2->consider,$point_2->date);
            $point_1=$point_2;

        }
        echo 'balance ok'."\n";
    }

    public function actionGen_dbx_finance_tbl(){
        $start_year=2014;
        $start_month=1;
        $current_year=(int)date('Y');
        $current_month=(int)date('m');
        for($i=$start_year;$i<=$current_year;$i++){
            if ($i>2015){
                break;
            }
            $j=($i==$start_year)? $start_month : 1;
            $jmax=($i==$current_year)? $current_month : 12;
            for(;$j<=$jmax;$j++){
                if(!DB\DbxFinance::find()->where(['year'=>$i,'month'=>$j])->exists()){
                    $dbx=new DB\DbxFinance(['year'=>$i,'month'=>$j,'downloaded'=>0]);
                    $dbx->save();
                }
            }
        }
    }

    public function actionGen_tcategory(){
        $fields=[
            'Мама'=>            ['name'=>'p_mom_multiple'],
            'Мама (PM)'=>       ['name'=>'p_mompm'],
            'Ученики'=>         ['name'=>'p_pupils'],
            'Другие доходы'=>   ['name'=>'p_other_multiple'],
            'Универ'=>          ['name'=>'m_university'],
            'MTI'=>             ['name'=>'m_mti'],
            'бенз'=>            ['name'=>'m_petrol'],
            'Моб'=>             ['name'=>'m_mobile','deleted'=>1],
            'Мобила'=>          ['name'=>'m_mobile'],
            'iPad'=>            ['name'=>'m_ipad'],
            'Гулянки'=>         ['name'=>'m_spend_multiple'],
            'Другие расходы'=>  ['name'=>'m_other_multiple'],
            'Корректировка'=>   ['name'=>'correcting']
        ];

        foreach($fields as $value => $params){
            if(preg_match('/^([pm])_/',$params['name'],$matches)){
                $params['sign']=($matches[1]=='p') ? '+' : '-';
                unset($matches);
            }
            //TODO: есть ли функция найтиИлиСоздать, чтобы две строчки ниже сделать одной
            $tc=DB\Column::find()->where(['value'=>$value])->one();
            $tc=is_object($tc) ? $tc : new DB\Column(['value'=>$value]);
            $tc->attributes=$params;
            $tc->save();
        }
    }

    public function actionInit(){

        $this->actionGen_dbx_finance_tbl();
       // $this->actionGen_tcategory();
    }

    private function uploadToDropbox($ffrom,$fto,$mode=null){
        if(is_null($mode)) $mode=dbx\WriteMode::force();

        $client=new  dbx\Client(\Yii::$app->params['dbx_token'],'directapp','UTF-8');
        $file=fopen($ffrom,'rb');
        $client->uploadFile($fto,$mode, $file);
        fclose($file);
    }
    public function options($actionID){
        $options=[];
        if( in_array($actionID,['per_day','per_month','index']))
            $options=['to_dbx','dfdf'];
        return  array_merge(
            parent::options($actionID),$options);
    }
    public $to_dbx=true;
    public function actionPer_day(){
        $start=microtime(true);
        $this->ActionDbxDownload();
        $this->ActionXlsm2csv();
        $this->ActionCsv2db();
        $this->ActionBalance_check();
        $time=microtime(true) - $start;
        $status='OK';
        $lastrec=DB\Record::find()->orderBy(['date' => SORT_DESC ])->one();
        $file=fopen(Yii::getAlias('@temp/records.log'),'ab+');
        fwrite($file,date('Y-m-d H:i:s').' '.__FUNCTION__.' '.$time.' '.$status
            .' last rec in db: '.$lastrec->date."\n");

        fclose($file);
        // upload log file to  dropbox
        if($this->to_dbx)
            $this->uploadToDropbox(Yii::getAlias('@temp/records.log'),'/records.log');
        echo 'Last record: '.$lastrec->date."\n";
    }

    public function actionPer_month(){
        $start=microtime(true);
        $this->ActionGen_dbx_finance_tbl();
        $time=microtime(true) - $start;

        $status='OK';
        $file=fopen(Yii::getAlias('@temp/records.log'),'ab+');
        fwrite($file,date('Y-m-d H:i:s').' '.__FUNCTION__.' '.$time.' '.$status."\n");
        fclose($file);

        // upload log file to  dropbox
        if($this->to_dbx)
            $this->uploadToDropbox(Yii::getAlias('@temp/records.log'),'/records.log');
    }

    public function actionGeneratexlsx($year='',$month=''){
        $year=empty((int) $year) ? date('Y') : (int) $year;
        $month=(int)(empty((int) $month) ? date('m') : $month);

        $date = new \DateTime("$year-$month-1");
        $date->sub(new \DateInterval('P1D'));

        //$bc=DB\BalanceCheck::findOne(['date'=> $date->format('Y-m-d')]);
        //$start_sum=is_object($bc) ? $bc->realmoney : $start_sum="";
        $start_sum=0;
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
        $monthstr=sprintf("%02d", $month);
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
        $output=Yii::getAlias('@temp/'.$year.'.'.$monthstr.'.xlsx');
        $objWriter->save($output);
    }



}