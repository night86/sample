<?php

use common\components\user\UserService;
use yii\swiftmailer\Mailer;

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class' => Mailer::class,
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            // TODO: mailsender configuration
            'transport' => [
                'class'     => 'Swift_SmtpTransport',
                'host'      => '127.0.0.1',
                'username'  => '',
                'password'  => '',
                'port'      => '1025',
                'encryption' => false
            ]
        ],
    ],
    'container' => [
        'singletons' => [
            UserService::class
        ]
    ]
];
