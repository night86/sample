<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace backend\versions\oauth2\controllers;

use filsh\yii2\oauth2server\controllers\RestController;
use filsh\yii2\oauth2server\Module as OAuthModule;
use filsh\yii2\oauth2server\Response;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\UnauthorizedHttpException;

/**
 * Class AuthenticationController
 * @package backend\versions\oauth2\controllers
 */
class AuthenticationController extends RestController
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'corsFilter' => [
                'class' => Cors::class // some custom config inside the class
            ],
        ]);
    }

    /**
     * @return array
     */
    public function actionOptions(): array
    {
        Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', ['OPTIONS', 'POST']));
        return [];
    }

    /**
     * Authenticates the user and returns with an access token
     *
     * @return mixed
     * @throws UnauthorizedHttpException
     * @throws \ReflectionException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     
     */
    public function actionToken()
    {
        // Run default action
        $result = parent::actionToken();

        /** @var OAuthModule $module */
        $module = $this->module;

        /** @var Response $response */
        $response = $module->getServer()->getResponse();

        Yii::$app->response->setStatusCode($response->getStatusCode());

        if ((int)$response->getStatusCode() === 401) {
            throw new UnauthorizedHttpException($result['error_description']);
        }

        return $response;
    }
}