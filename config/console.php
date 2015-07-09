<?php

Yii::setAlias('@tests', dirname(__DIR__) . DIRECTORY_SEPARATOR .'tests');
Yii::setAlias('@data', dirname(__DIR__) . DIRECTORY_SEPARATOR  . 'data');
Yii::setAlias('@finance_download_path', Yii::getAlias('@data').DIRECTORY_SEPARATOR.'finance_download');
Yii::setAlias('@finance_csv', Yii::getAlias('@data').DIRECTORY_SEPARATOR.'finance_csv');



$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

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
        'db' => $db,
    ],
    'params' => $params,
];
