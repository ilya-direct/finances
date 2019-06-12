<?php

Yii::setAlias('@temp', dirname(__DIR__) . DIRECTORY_SEPARATOR .'temp');
Yii::setAlias('@data', dirname(__DIR__) . DIRECTORY_SEPARATOR  . 'temp'. DIRECTORY_SEPARATOR .'data');
Yii::setAlias('@templates', dirname(__DIR__) . DIRECTORY_SEPARATOR  . 'templates');
Yii::setAlias('@finance_download_path', Yii::getAlias('@data').DIRECTORY_SEPARATOR.'finance_download');
Yii::setAlias('@finance_csv', Yii::getAlias('@data').DIRECTORY_SEPARATOR.'finance_csv');
Yii::setAlias('@integrations', dirname(__DIR__) . '/integrations');

$env = Dotenv\Dotenv::create(dirname(__DIR__));
$env->load();

$params = require(__DIR__ . '/params.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'dropbox' => [
            'class' => \integrations\dropbox\DropboxApi::class,
            'accessToken' => $params['dbxToken'],
        ],
        'dbFin2014'=> require(__DIR__ . '/dbFin2014.php'),
        'dbFin2016'=> require(__DIR__ . '/dbFin2016.php'),
    ],
    'params' => $params,
];
