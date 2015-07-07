<?php

namespace app\commands;
use \Dropbox as dbx;

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
    public function actionDbxDownload()
    {
	    $token='OprJKfb4QroAAAAAAAAFZw2tIxGlGCVvvqWn-58KmhEhazh_vSdUvUtpJ_JBTZDS';
	    $client=new  dbx\Client($token,'directapp','UTF-8');

	    $download_path=__DIR__.DIRECTORY_SEPARATOR.'finance_download';
	    if(!is_dir($download_path) and !mkdir($download_path,0777,true))
		    throw new Exception('can\'t create download directory');

	    $finances=$client->getMetadataWithChildren('/finances')['contents'];
	    $finances=array_map(function($el){
		    $el['modified']=dbx\Client::parseDateTime($el['modified'])->format("Y-m-d H:i:s");
		    return $el;
	    },$finances);
	    $recs=$DB->get_records('dbx_finance');
	    foreach( $recs as $rec){
		    $yearmonth=$rec->year.'.'.$rec->month;
		    $info=search_info($yearmonth);
		    if(!$info) throw new Exception('not found file '. $yearmonth);

		    $download_filename=$download_path.DIRECTORY_SEPARATOR.$yearmonth.'.raw';
		    if($info['modified']>$rec->download_time || $rec->exists==0){
			    $rec->download_time=date('Y-m-d H:i:s');
			    $rec->modified_time=$info['modified'];
			    $rec->file_name=$info['path'];
			    $rec->exists=1;
			    $rec->in_db=0;
			    $rec->csv_converted=0;
			    if(!is_null($client->getFile($info['path'],fopen($download_filename,'wb')))){
				    $DB->update_record('dbx_finance',$rec);
				    echo "$yearmonth updated\n";
			    }
		    }
	    }

	    function search_info($pattern){
		    global $finances;
		    foreach($finances as $fin){
			    if(preg_match('/'.$pattern.'/',$fin['path'])){
				    return $fin;
			    }
		    }
		    return false;
	    }
    }
}
