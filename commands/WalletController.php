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
				$objWriter->setPreCalculateFormulas(false);
				$output_filepath=$output_path.'/'.$file_name.'.csv';
				$objWriter->save($output_filepath);
				$rec->csv_converted=1;
				$rec->update();
				echo "file $file_name converted\n";
			}else
				//$DB->set_field('dbx_finance','exists',0,array('id'=>$rec->id));
				throw new \Exception("file $input_filepath not found\n");
		}
	}
}