<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

return [
    'components' => [
        'mongodb'   => [
            'class'     => '\yii\mongodb\Connection',
            'dsn'       => 'mongodb://127.0.0.1:27017/admy-functional-tester'
        ],
        'cache' => [
            'class' => 'yii\mongodb\Cache',
        ],
        // MailCatcher config
        'mailer'    => [
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
    ]
];