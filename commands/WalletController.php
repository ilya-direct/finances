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
	    $objPHPExcel = \PHPExcel_IOFactory::load($DIR. '/ssss');
	    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, false, true, true);
	    //$finances=$client->getMetadataWithChildren('/finances')['contents'];
	    //x();
	    $m=function (){
		    print('xxx');
	    };
	    $m();
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

	    $recs=DB\DbxFinance::find()->orderBy('year ASC, month ASC')->all();
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

	public function actionXlsm2csv(){

		$input_path=Yii::getAlias('@finance_download_path');
		if(!is_dir($input_path))
			throw new \Exception('can\'t find input directory');

		$output_path=Yii::getAlias('@finance_csv');
		if(!is_dir($output_path) and !\yii\helpers\FileHelper::createDirectory($output_path,0777,true))
			throw new \Exception('can\'t create output directory');


		$recs=DB\DbxFinance::find()->where(['exists'=>1,'csv_converted'=>0])->orderBy('year ASC, month ASC')->all();
		foreach($recs as $rec){
			$file_name=$rec->year.'.'.$rec->monthStr;
			$input_filepath=$input_path.'/'.$file_name.'.xlsm';
			if(file_exists($input_filepath)){
				$objPHPExcel = \PHPExcel_IOFactory::load($input_filepath);
				$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
				$objWriter->setDelimiter(";");
				$objWriter->setEnclosure("");
				$objPHPExcel->getActiveSheet()->getStyle('C:C')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
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
				$field_name_obj=(DB\TransactionCategory::find()->select('name')->where(['value'=>$headers[$i]])->one());
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
			->where(['exists'=>1,'csv_converted'=>1,'in_db'=>0])
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
									'realmoney'=>$data[$i],
									'diff'=>$data[array_search('difference',$headers)]
								];
								$balance_check->save();
								unset($balance_check);
							}
						}
						continue;
					}
					if(empty($data[$i])){
						//TODO: сделать одним запросом
						$tcategories=DB\TransactionCategory::find()->select('id')->where(['name'=>$headers[$i]])->all();
						$tcategories=array_map(function($tc){ return $tc->id;},$tcategories);
						//TODO: сделать более безопасное удаление !!
						if(!empty($tcategories))
							DB\Record::deleteAll('date="'.$date.'" and tcategory in ('.implode(',',$tcategories).')');
						unset($tcategories);
						continue;
					}
					if(count($header_parts)===2){
						DB\Record::insert_transaction_single($date,$headers[$i],$data[$i]);
					}elseif(count($header_parts)===3 && $header_parts[2]=='multiple'){
						$coins=explode('|',$data[$i]);
						$coins_desc=explode('|',$data[$i+1]);
						if (count($coins)!=count($coins_desc))
							throw new \Exception("Неверная запись {$date} : {$data[$i]} {$data[$i+1]} ");
						$entries=array();
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

			fclose($file_handle);
			echo "file $yearmonth imported to db \n";
			//updateByPk подошло бы
			DB\DbxFinance::updateAll(['in_db'=>1],['id'=>$rec->id]);
		}

		// Удаление названий транзакций, на которых нет ссылки в тбл record
		//TODO: оптимизировать
		$item_ids=DB\Record::find()->select('itemid')->distinct();
		if(!empty($item_ids))
			DB\Item::deleteAll(['not in','id',$item_ids]);
	}

	public function actionBalance_check(){
		function get_correcting_sum($y,$m){
			\Yii::$app->db;
			$db=new \yii\db\Connection(\Yii::$app->db);
			$correcting=$db->createCommand("
				select r.sum from record r left join transaction_category tc on tc.id=r.tcategory
				where tc.name='correcting' and year(r.`date`)=:year and month(r.date)=:month",['year'=>$y,'month'=>$m])->queryOne();
			return $correcting['sum'];
		}

		function compare_values($actual,$mustbe,$date){
			if ($actual!=$mustbe)
				throw new \Exception("$date Сумма по расчету: $actual Должна быть: {$mustbe} false\n");
		}
		if(!DB\BalanceCheck::find()->where('year(date)=2013')->exists())
			throw new \Exception('not initialized with script init');

		$points=DB\BalanceCheck::find()->orderBy('date')->all();;
		$point_1=array_shift($points);
		$total_sum=$point_1->consider;
		foreach($points as $point_2){
			$sum=DB\Record::find()->select(['sum(sum) as sum'])->where(['>','date',$point_1->date])
				->andWhere(['<=','date',$point_2->date])->one();
			$total_sum+=$sum->sum;

			preg_match('/^([\d]{4})\-([\d]{2})-([\d]{2})/',$point_2->date,$matches);
			$y=$matches[1]; $m=$matches[2]; $d=$matches[3];
			$maxday=date('d',mktime(0,0,0,$m+1,0,$y));
			if ($d==$maxday){
				$correction=get_correcting_sum($y,$m);
				compare_values($total_sum-$correction,$point_2->consider,$point_2->date);
			}else
				compare_values($total_sum,$point_2->consider,$point_2->date);
			$point_1=$point_2;

		}
		echo 'checked'."\n";
	}

	public function actionGen_dbx_finance_tbl(){
		$start_year=2014;
		$start_month=1;
		$current_year=(int)date('Y');
		$current_month=(int)date('m');
		for($i=$start_year;$i<=$current_year;$i++){
			$j=($i==$start_year)? $start_month : 1;
			$jmax=($i==$current_year)? $current_month : 12;
			for(;$j<=$jmax;$j++){
				if(!DB\DbxFinance::find()->where(['year'=>$i,'month'=>$j])->exists()){
					$dbx=new DB\DbxFinance(['year'=>$i,'month'=>$j,'exists'=>0]);
					$dbx->save();
				}
			}
		}
	}

	public function actionGen_tcategory(){
		$fields=[
			'Мама'=>            ['name'=>'p_mom_multiple','sort'=>2],
			'Мама (PM)'=>       ['name'=>'p_mompm','sort'=>3],
			'Ученики'=>         ['name'=>'p_pupils','sort'=>4],
			'Другие доходы'=>   ['name'=>'p_other_multiple','sort'=>5],
			'Универ'=>          ['name'=>'m_university','sort'=>6],
			'MTI'=>             ['name'=>'m_mti','sort'=>7],
			'бенз'=>            ['name'=>'m_petrol','sort'=>8],
			'Моб'=>             ['name'=>'m_mobile','sort'=>8,'deleted'=>1],
			'Мобила'=>          ['name'=>'m_mobile','sort'=>9],
			'iPad'=>            ['name'=>'m_ipad','sort'=>10],
			'Гулянки'=>         ['name'=>'m_spend_multiple','sort'=>11],
			'Другие расходы'=>  ['name'=>'m_other_multiple','sort'=>12],
			'Корректировка'=>   ['name'=>'correcting','sort'=>13]
		];

		foreach($fields as $value => $params){
			if(preg_match('/^([pm])_/',$params['name'],$matches)){
				$params['sign']=($matches[1]=='p') ? '+' : '-';
				unset($matches);
			}
			//TODO: есть ли функция найтиИлиСоздать, чтобы две строчки ниже сделать одной
			$tc=DB\TransactionCategory::find()->where(['value'=>$value])->one();
			$tc=is_object($tc) ? $tc : new DB\TransactionCategory(['value'=>$value]);
			$tc->attributes=$params;
			$tc->save();
		}
	}

	public function actionInit(){
		$init_params=[
			'date'=>'2013-12-31',
			'realmoney'=>15114,
			'consider'=>15114,'diff'=>0
		];
		if(!DB\BalanceCheck::find()->where($init_params)->exists()){
			$bc=new DB\BalanceCheck($init_params);
			$bc->save();
		}
		$this->actionGen_dbx_finance_tbl();
		$this->actionGen_tcategory();
	}
}