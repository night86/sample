<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

use common\components\logs\ExtendedFileTargetLog;
use yii\swiftmailer\Mailer;

return [
    'components' => [
        'mongodb'   => [
            'class'     => '\yii\mongodb\Connection',
            'dsn'       => 'mongodb://127.0.0.1:27017/admy_functional_test'
        ],

        'cache' => [
            'class' => 'yii\mongodb\Cache',
        ],

        // MailCatcher config
        'mailer'    => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'class'     => 'Swift_SmtpTransport',
                'host'      => '127.0.0.1',
                'username'  => '',
                'password'  => '',
                'port'      => '1025',
                'encryption' => false
            ]
        ],
        'log' => [
            'traceLevel' => 10, // YII_DEBUG ? 10 : 0
            'flushInterval' => 1,
            'targets' => [
                'file' => [
                    'exportInterval' => 1,
                    'class' => ExtendedFileTargetLog::class,
                    'levels' => ['trace', 'info'],
                    'categories' => ['yii\*', 'application'],
                    'except' => ['yii\web\UrlManager:*', 'letyii\*'],
                    'logFile' => __DIR__ . '/../_log/app-tests-debug.log',
                ],
                [
                    'exportInterval' => 1,
                    'logVars' => [],
                    'class' => ExtendedFileTargetLog::class,
                    'levels' => ['error', 'warning'],
                    'categories' => ['yii\*', 'application'],
                    'except' => ['yii\mongodb\*', 'yii\i18n\*', 'yii\web\UrlManager:*', 'letyii\*'],
                    'logFile' => __DIR__ . '/../_log/app-tests.log',
                ],
            ],
        ],
    ]
];