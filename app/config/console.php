<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
require_once __DIR__ . '/_localConfig.php';

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
        'applePush' => [
            'class'         => '\app\components\ApplePushNotification',
            'apnsHostProd'  => 'gateway.push.apple.com', //gateway.sandbox.push.apple.com/gateway.push.apple.com
            'apnsHostDev'   => 'gateway.sandbox.push.apple.com', //gateway.sandbox.push.apple.com/gateway.push.apple.com
            'apnsPort'      => 2195,
            'apnsCertProd'  => dirname(__DIR__) . '/data/certificates/apple_push_notification_production.pem',
            'apnsCertDev'   => dirname(__DIR__) . '/data/certificates/apple_push_notification_developent.pem',
            //'apnsPassphrase'=> dirname(__DIR__) . '/data/certificates/passphare',
            'timeout'       => 500000, //microseconds
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        'errors' => [
            'class' => '\app\components\Errors',
        ],
        'redisOrm' => [
            'class' => '\app\components\Redis',
        ],

        'console' => [
            'class' => '\app\components\TConsoleRunner'
        ],

        'pathTransformer' => [
            'class' => '\app\components\PathTransformer',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'logVars' => ['error'],
                    'logFile' => '@runtime/logs/error.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning'],
                    'logVars' => ['warning'],
                    'logFile' => '@runtime/logs/warning.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => ['show'],
                    'categories' => ['show'],
                    'logFile' => '@runtime/logs/show.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => ['delayed'],
                    'categories' => ['delayed'],
                    'logFile' => '@runtime/logs/delayed.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => ['ts_publish'],
                    'categories' => ['ts_publish'],
                    'logFile' => '@runtime/logs/ts_publish.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => ['soket_restart'],
                    'categories' => ['soket_restart'],
                    'logFile' => '@runtime/logs/soket_restart.log'
                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['info'],
                    'categories' => ['db_log'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => $params,
];
