<?php

namespace integrations\dropbox;


use linslin\yii2\curl\Curl;
use yii\base\Exception;
use yii\base\BaseObject;
use yii\helpers\Json;

class DropboxApi extends BaseObject
{
    public $accessToken;
    
    protected $curlContent;
    protected $curlContentHost = 'https://content.dropboxapi.com';
    
    protected $curlApi;
    protected $curlApiHost = 'https://api.dropboxapi.com';
    
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->accessToken = $config['accessToken'];
        
        $this->curlApi = new Curl();
        // TODO add certificates!!!!
        $this->curlApi->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curlApi->setOption(CURLOPT_SSL_VERIFYPEER, 0);
        $this->curlApi->setHeader('Content-Type', 'application/json');
        $this->curlApi->setHeader('Authorization', 'Bearer ' . $this->accessToken);
        
        $this->curlContent = new Curl();
        // TODO add certificates!!!!
        $this->curlContent->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curlContent->setOption(CURLOPT_SSL_VERIFYPEER, 0);
        $this->curlContent->setHeader('Authorization', 'Bearer ' . $this->accessToken);
    }
    
    public function filesListFolder($path)
    {
        $this->curlApi->setRequestBody(Json::encode([
            'path' => $path,
        ]));
        $this->curlApi->post($this->curlApiHost . '/2/files/list_folder');
        
        $response = $this->normalizeResponse($this->curlApi);
        
        return $response['entries'];
    }
    
    public function filesDownload($path, $downloadFilePath)
    {
        $this->curlContent->unsetHeader('Dropbox-API-Arg');
        $this->curlContent->setHeader('Dropbox-API-Arg', Json::encode([
            'path' => $path,
        ]));
        $this->curlContent->setHeader('Content-Type', "text/plain");
        $this->curlContent->setRequestBody('');
    
    
        $this->curlContent->post($this->curlContentHost . '/2/files/download');
    
        $response = $this->normalizeResponse($this->curlContent);
        $success = file_put_contents($downloadFilePath, $response);
        
        // downloaded file info
//        $fileInfo = $this->curlContent->responseHeaders['dropbox-api-result'];
        
        return !!$success;
    }
    
    public function filesUpload($dropboxPath, $localFilePath)
    {
        $this->curlContent->unsetHeader('Dropbox-API-Arg');
        $this->curlContent->setHeader('Dropbox-API-Arg', Json::encode([
            'path' => $dropboxPath,
        ]));
        $this->curlContent->setHeader('Content-Type', 'text/plain; charset=dropbox-cors-hack');
        $this->curlContent->setRequestBody(file_get_contents($localFilePath));
    
        $this->curlContent->post($this->curlContentHost . '/2/files/upload');
    
        $response = $this->normalizeResponse($this->curlContent);
        
        return true;
    }
    
    public function filesSearch($folder, $query)
    {
        $this->curlApi->setRequestBody(Json::encode([
            'path' => $folder,
            'query' => $query,
        ]));
        $this->curlApi->post($this->curlApiHost . '/2/files/search');
    
        $response = $this->normalizeResponse($this->curlApi);
    
        return !!count($response['matches']);
    }
    
    /**
     * @param $curl Curl
     * @return mixed
     * @throws Exception
     */
    private function normalizeResponse($curl)
    {
        if ($curl->responseCode === 200) {
            $contentType = empty($curl->responseHeaders['Content-Type']) ? false : strtolower($curl->responseHeaders['Content-Type']);
            if ($contentType &&  ($contentType == 'application/json')) {
    
                return Json::decode($curl->response);
            } else {
                
                return $curl->response;
            }
        } else {
            throw new Exception('Dropbox: ' . $curl->response, $curl->responseCode);
        }
    }
    
    
    static public function parseDateTime($dateTimeString)
    {
        $datetime =  \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $dateTimeString);
        if (!$datetime) {
            throw new Exception('Dropbox: Bad date/time');
        }
        return $datetime->format('Y-m-d H:i:s');
    }
}