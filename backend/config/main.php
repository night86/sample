<?php

use common\components\logs\ExtendedFileTargetLog;
use common\models\User;
use common\components\request\WebRequest;
use filsh\yii2\oauth2server\models\OauthAuthorizationCodes;
use filsh\yii2\oauth2server\models\OauthScopes;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Storage\Memory;
use synida\yii2\mongodb\oauth\model\OauthAccessTokens;
use synida\yii2\mongodb\oauth\grant\RefreshTokenGrantType;
use synida\yii2\mongodb\oauth\model\OauthClients;
use synida\yii2\mongodb\oauth\model\OauthRefreshTokens;
use synida\yii2\mongodb\oauth\storage\Yii2MongoDB;
use yii\web\JsonParser;
use yii\web\Response;
use backend\versions\common\Module as CommonModule;
use backend\versions\v1\Module as V1Module;
use backend\versions\oauth2\Module as OAuthModule;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$routes = require __DIR__ . '/routes.php';

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'common' => [
            'class' => CommonModule::class
        ],
        'v1' => [
            'class' => V1Module::class
        ],
        'oauth2' => [
            'class' => OAuthModule::class,
            'tokenParamName' => 'accessToken',
            'tokenAccessLifetime' => $params['api']['oauth2']['token_access_lifetime'],
            'storageMap' => [
                'user_credentials' => User::class,
                'access_token' => Yii2MongoDB::class,
                'authorization_code' => Yii2MongoDB::class,
                'client_credentials' => Yii2MongoDB::class,
                'refresh_token' => Yii2MongoDB::class,
                'client' => Yii2MongoDB::class,
                'jwt_bearer' => Memory::class,
                'public_key' => Memory::class,
                'scope' => Memory::class
            ],
            'modelMap' => [
                'OauthClients' => OauthClients::class,
                'OauthAccessTokens' => OauthAccessTokens::class,
                'OauthAuthorizationCodes' => OauthAuthorizationCodes::class,
                'OauthRefreshTokens' => OauthRefreshTokens::class,
                'OauthScopes' => OauthScopes::class,
            ],
            'grantTypes' => [
                'client_credentials' => [
                    'class' => ClientCredentials::class,
                    'allow_public_clients' => false
                ],
                'user_credentials' => [
                    'class' => UserCredentials::class
                ],
                'refresh_token' => [
                    'class' => RefreshTokenGrantType::class,
                    'always_issue_new_refresh_token' => true
                ]
            ],
        ]
    ],
    'components' => [
        'request' => [
            'class' => WebRequest::class,
            'enableCookieValidation' => false,
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => JsonParser::class,
            ]
        ],

        'response' => [
            'format' => Response::FORMAT_JSON,
            'class' => Response::class,
            'on beforeSend' => static function ($event) {
                /** @var yii\web\Response $response */
                $response = $event->sender;

                $response->format = Response::FORMAT_JSON;

                //it needs for the OAUTH to enable sending the Authorization header
                $response->getHeaders()->set(
                    'Access-Control-Allow-Headers',
                    implode(', ', ['Authorization', 'sessionID'])
                );
                // TODO: restrict cross domain origin; use local parameter per server
                //it needs for the cross domain policy
                $response->getHeaders()->set('Access-Control-Allow-Origin', '*');
                //it needs for the cross domain policy
                $response->getHeaders()->set(
                    'Access-Control-Allow-Methods',
                    implode(', ', ['POST', 'GET', 'PUT', 'DELETE', 'OPTIONS'])
                );
                // TODO: language setter
                // API always returns in english
                $response->getHeaders()->set('Content-Language', 'en');
            },
            'on afterPrepare' => static function ($event) {
                $response = $event->sender;

                if ($response->content === 'null') {
                    $response->content = '';
                }

                if ($response->content === '[]') {
                    $response->content = json_encode([], JSON_THROW_ON_ERROR);
                }

                $content = json_decode($response->content, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($content)
                    && json_last_error() === JSON_ERROR_NONE
                    && count($content) === 1
                    && isset($content[0])
                ) {
                    $content = $content[0];
                    if (is_numeric($content) || is_string($content)) {
                        $response->content = json_encode($content, JSON_THROW_ON_ERROR);
                    }
                }
            }
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => ExtendedFileTargetLog::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'cache' => false,
            'rules' => $routes,
        ],
    ],
    'params' => $params,
];
