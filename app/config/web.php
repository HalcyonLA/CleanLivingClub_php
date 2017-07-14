<?php
require_once __DIR__ . '/_localConfig.php';

$config = [
    'id' => 'TimeBot',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'name' => 'TimeBot',

    'modules' => [
        'apiVersion1' => [
            'class' => 'app\modules\api_v1\Api',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'mtvgAbriMtuLxnKWRuc8H50n4lDBmJMC',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],

        'redisOrm' => [
            'class' => '\app\components\Redis',
        ],
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'halcyon.test@gmail.com',
                'password' => 'h@lcy0n7',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
        'mailerHelper' => [
            'class' => 'app\components\MailerHelper',
            'templatePath' 	=> dirname(__FILE__) . '/../views/mail-templates/',
        ],
        'facebook' => [
            'class' => '\app\components\Facebook',
        ],
        'facebookPicture' => [
            'class' => '\app\components\FacebookPicture',
            'bigWidth' => 1024,
            'smallWidth' => 150
        ],
        'twitter' => [
            'class' => 'richweber\twitter\Twitter',
            'consumer_key' => 'wqu33WhpeGJvFQ5pG4kZ90PAf',
            'consumer_secret' => 'oabOhP595pTKudGzvBs7U4qJokMTUBfG2PE3Pj9A0ZYtDgx0bY',
            'callback' => 'YOUR_TWITTER_CALLBACK_URL',
        ],
        'tw' => [
            'class' => '\app\components\Twitter',
        ],
        'errors' => [
            'class' => '\app\components\Errors',
        ],
        'imgProcessor' => [
            'class' => '\app\components\ImgProcessor',
            'defaultDir' => '/user_pictures/',
            'bigWidth' => 1024,
            'smallWidth' => 150,
            'timesyncThumbWith' => 450
        ],

        'console' => [
            'class' => '\app\components\TConsoleRunner'
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

        'videoProcessor' => [
            'class' => '\app\components\VideoProcessor',
            'defaultDir' => '/user_videos/',
        ],
        'pathTransformer' => [
            'class' => '\app\components\PathTransformer',
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Disable index.php
            'showScriptName' => false,
            // Disable r= routes
            'enablePrettyUrl' => true,
            'rules' => [
                'api/<_version:\d+>/<controller:(\w|\-)+>' 				=> 'apiVersion<_version>/<controller>',
                'api/<_version:\d+>/<controller:(\w|\-)+>/<action:(\w|\-)+>' => 'apiVersion<_version>/<controller>/<action>',
                '<controller:\w+>/<id:\d+>'                             => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'                => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>'                         => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:[\w\_\-\d]+>'          => '<controller>/<action>',
            ],
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
                    'logVars' => ['auth'],
                    'categories' => ['auth'],
                    'logFile' => '@runtime/logs/auth.log'
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
                    'logVars' => ['related'],
                    'categories' => ['related'],
                    'logFile' => '@runtime/logs/related.log'
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
    'params' => require(__DIR__ . '/params.php'),
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',

    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*']
    ];
}

return $config;
