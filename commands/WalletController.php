<?php

namespace app\commands;
use \Dropbox as dbx;
use Yii;
use app\models\WalletDB as DB;

class WalletController extends \yii\console\Controller
{
    public function actionIndex()
    {
	    $DIR=dirname(__FILE__);
	    $token='OprJKfb4QroAAAAAAAAFZw2tIxGlGCVvvqWn-58KmhEhazh_vSdUvUtpJ_JBTZDS';
	    $client=new  dbx\Client($token,'directapp','UTF-8');
	    $objPHPExcel = \PHPExcel_IOFactory::load($DIR. '/2014.10.xlsm');
	    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, false, true, true);
	    $finances=$client->getMetadataWithChildren('/finances')['contents'];
	    print_r($finances);
    }
    public function actionDbxdownload()
    {
	    //TODO: добавить константу token в конфиг
	    $token='OprJKfb4QroAAAAAAAAFZw2tIxGlGCVvvqWn-58KmhEhazh_vSdUvUtpJ_JBTZDS';
	    $client=new  dbx\Client($token,'directapp','UTF-8');

	    $download_path=Yii::getAlias('@finance_download_path');

		//TODO: Заменить is_dir функцией из библиотеки YII
	    if(!is_dir($download_path) and !\yii\helpers\FileHelper::createDirectory($download_path,0777,true))
		    throw new \Exception('can\'t create download directory');


	    $finances=$client->getMetadataWithChildren('/finances')['contents'];
	    $finances=array_map(function($el){
		    $el['modified']=dbx\Client::parseDateTime($el['modified'])->format("Y-m-d H:i:s");
		    return $el;
	    },$finances);

	    $recs=DB\DbxFinance::find()->all();
	    foreach( $recs as $rec){

		    //TODO: убрать строку ниже, добавить её в AR форматный вывод поля
		    $rec->month=str_replace(' ','0',\sprintf('%2.d', $rec->month));
		    $yearmonth=$rec->year.'.'.$rec->month;
		    //TODO: решить проблему с API и убрать функцию SEARCH_INFO
		    $info=$this->search_info($yearmonth,$finances);
		    if(!$info) throw new \Exception('not found file '. $yearmonth);

		    $download_filename=$download_path.DIRECTORY_SEPARATOR.$yearmonth.'.xlsm';
		    if($info['modified']>$rec->download_time || $rec->exists==0){
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
	private function search_info($pattern,$finances){
		foreach($finances as $fin){
			if(preg_match('/'.$pattern.'/',$fin['path'])){
				return $fin;
			}
		}
		return false;
	}


	public function actionXlsm2csv(){

		$input_path=Yii::getAlias('@finance_download_path');
		if(!is_dir($input_path))
			throw new \Exception('can\'t find input directory');

		$output_path=Yii::getAlias('@finance_csv');
		if(!is_dir($output_path) and !\yii\helpers\FileHelper::createDirectory($output_path,0777,true))
			throw new \Exception('can\'t create output directory');


		$recs=DB\DbxFinance::findAll(['exists'=>1,'csv_converted'=>0]);
		foreach($recs as $rec){
			//TODO: убрать строку ниже, добавить её в AR форматный вывод поля
			$rec->month=str_replace(' ','0',\sprintf('%2.d', $rec->month));
			$file_name=$rec->year.'.'.$rec->month;
			$input_filepath=$input_path.'/'.$file_name.'.xlsm';
			if(file_exists($input_filepath)){
				$objPHPExcel = \PHPExcel_IOFactory::load($input_filepath);
				$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
				$objWriter->setDelimiter(";");
				$objWriter->setEnclosure("");
//				$objWriter->setPreCalculateFormulas(false);
				$output_filepath=$output_path.'/'.$file_name.'.csv';
				$objWriter->save($output_filepath);
				$rec->csv_converted=1;
				$rec->update();
				echo "file $file_name converted\n";
			}else
				throw new \Exception("file $input_filepath not found\n");
		}
	}

	public function actionCsv2db(){
		$input_path=Yii::getAlias('@finance_csv');
		if(!is_dir($input_path))
			throw new \Exception('can\'t find input directory in csv2db.php');

		$recs=DB\DbxFinance::findAll(['exists'=>1,'csv_converted'=>1,'in_db'=>0]);

		foreach($recs as $rec){
			$yearmonth=$rec->year.'.'.$rec->month;
			$input_filepath=$input_path.'/'.$yearmonth.'.csv';
			if(!file_exists($input_filepath))
				throw new Exception("file $input_filepath not found");

			$file_handle=fopen($input_filepath,'r');
			if(!$file_handle)
				throw new Exception("Не удалось открыть файл $input_filepath!");

			$headers_1=get_table_headers($file_handle,"ТС по расчету");
			if ($headers_1===false)
				throw new Exception("Строка с корректировкой баланса не найдена $yearmonth");
			array_to_utf8($headers_1);
			fix_headers($headers_1);

			$headers_2=get_table_headers($file_handle,"Дата");
			if ($headers_2===false)
				throw new Exception("Строка с основным заголовком не найдена $yearmonth");
			array_to_utf8($headers_2);
			fix_headers($headers_2);

			$headers=merge_headers($headers_1,$headers_2);
			unset($headers_1); unset($headers_2);

			$date_index=array_search('date',$headers);
			if ($date_index===false)
				throw new Exception('В заголовках нет даты '.$yearmonth);

			$maxday=date('d',mktime(0,0,0,$rec->month+1,0,$rec->year));

			for($current_day=1;$current_day<=$maxday;$current_day++){
				$data=fgetcsv($file_handle,null,';');
				$date=$data[$date_index];
				if(!preg_match('/^([\d]{2})\.([\d]{2})\.([\d]{4})$/',$date,$matches) or
					$matches[1]!=$current_day || $matches[2]!=$rec->month || $matches[3]!=$rec->year) {
					throw new Exception('Дата '.$date.' не совпадает с ожидаемой в файле '.$input_filepath);
				}
				$date="{$rec->year}.{$rec->month}.{$current_day}";
				array_to_utf8($data);
				for($i=0;$i<count($headers);$i++){
					if ($headers[$i]===false) continue;
					$data[$i]=trim($data[$i]);
					$header_parts=explode('_',$headers[$i]);
					if (count($header_parts)==1){
						if($headers[$i]=='realmoney'){
							if(empty($data[$i])){
								$DB->delete_record_sql("delete from balance_check where date='$date'");
							}else{
								$table='balance_check';
								$params=array('date'=>$date,
									'consider'=>$data[array_search('countmoney',$headers)],
									'realmoney'=>$data[$i],
									'diff'=>$data[array_search('difference',$headers)]);
								$balance_check=$DB->get_record($table,array('date'=>$date));
								if(is_object($balance_check)){
									$params['id']=$balance_check->id;
									$DB->update_record($table,$params);
								}else{
									$DB->insert_record($table,$params);
								}
								unset($table); unset($params); unset($balance_check);
							}
						}
						continue;
					}
					if(empty($data[$i])){
						delete_transactions($date,$headers[$i]);
						continue;
					}
					$sign=$header_parts[0];
					if(count($header_parts)===2){
						insert_transaction_single($date,$headers[$i],$data[$i]);
					}elseif(count($header_parts)===3 && $header_parts[2]=='multiple'){
						$coins=explode('|',$data[$i]);
						$coins_desc=explode('|',$data[$i+1]);
						if (count($coins)!=count($coins_desc))
							throw new Exception("Неверная запись {$date} : {$data[$i]} {$data[$i+1]} ");
						$entries=array();
						for($j=0;$j<count($coins);$j++){
							$entry=new stdClass();
							$coins_desc[$j]=trim($coins_desc[$j]);
							if(empty($coins_desc[$j]))
								throw new Exception("Нет описания $date {$data[$i]} {$data[$i+1]}");
							$entry->sum=$coins[$j];
							$entry->item=$coins_desc[$j];
							$entries[]=clone $entry;
						}
						insert_transaction_multiple($date,$headers[$i],$entries);
						unset($entry); unset($entries);
					}
				}
			}
			$flags=[
				0b00001=>'Корректировка',
				0b00010=>'Всего получено',
				0b00100=>'Всего потрачено',
				0b01000=>'Стартовый капитал',
				0b10000=>'Конечный капитал'
			];
			$total_flag= 0b00000;
			while(($data=fgetcsv($file_handle,null,';'))!==false){
				to_utf8($data[$date_index]);
				if(in_array($data[$date_index],$flags)){
					$total_flag=$total_flag | array_search($data[$date_index],$flags);
					$max_date="$rec->year.$rec->month.$maxday";
					if($data[$date_index]==$flags[0b00001] && $DB->record_exists('balance_check',array('date'=>$max_date)))
						insert_transaction_single($max_date,'correcting',$data[$date_index+1],true);
				}
			}
			if (!($total_flag & 0b00001))
				throw new Exception('Отсутствуют данные о корректировке '."$rec->year-$rec->month-$maxday");

			fclose($file_handle);
			echo "file $yearmonth imported to db \n";
			$DB->set_field('dbx_finance','in_db',1,array('id'=>$rec->id));
		}
		delete_items_without_rec();

		function get_table_headers(&$handle,$needle){
			if (is_null($needle) or empty($needle)) return false;
			while(($data=fgetcsv($handle,null,';'))!==false){
				foreach($data as $cell){
					to_utf8($cell);
					if ($cell===$needle){
						return $data;
					}
				}
			}
			return false;
		}


		function fix_headers(&$headers){
			Global $DB;
			for($i=0; $i<count($headers); $i++){
				if($DB->record_exists('transaction_category',array('value'=>$headers[$i]))){
					$field_name=$DB->get_field('transaction_category','name',array('value'=>$headers[$i]));
					$field_array=explode('_',$field_name);
					if(count($field_array)<=2){
						$headers[$i]=$field_name;
					}elseif(count($field_array)===3 && $field_array[2]=='multiple'){
						$headers[$i]=$field_name;
						if(!array_key_exists($i+1,$headers) || $headers[$i+1]!="")
							throw new Exception('Неверный формат колонок (формат заголовка таблицы)');
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
		function to_utf8(&$str){
			$encoding=mb_detect_encoding($str);
			if($encoding=="UTF-8") return;
			iconv( $encoding,'UTF-8',$str);
		}

		function array_to_utf8(array &$arr){
			foreach($arr as &$item){
				to_utf8($item);
			}
		}

		function merge_headers($h1,$h2){
			global $yearmonth;
			$n=(count($h1)>count($h2)) ? count($h1) : count($h2);
			$result=array();
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
						throw new Exception("Конфликт заголовков таблицы при слиянии $h1[$i] $h2[$i] $yearmonth");
				}
			}
			return $result;
		}
		function delete_items_without_rec(){
			Global $DB;
			$ids=$DB->get_fieldset_sql('select distinct itemid from record');
			if(!empty($ids))
				$DB->delete_record_sql('delete from item where id not in ('.implode(',',$ids).')');
		}
	}
}