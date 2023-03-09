<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace backend\versions\v1\controllers;

use backend\versions\common\controllers\SecureRestController;
use common\components\user\UserService;
use common\models\search\UserSearch;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\web\ServerErrorHttpException;

/**
 * Class UserController
 * @package backend\versions\v1\controllers
 */
class UserController extends SecureRestController
{
    /**
     * Defined standard model class for default operations in the parent class
     *
     * @var string|null
     */
    protected ?string $modelClass = User::class;

    /**
     * Search class model name
     *
     * @var string|null
     */
    protected ?string $searchModelClass = UserSearch::class;

    /**
     * User operations service class
     *
     * @var UserService
     */
    protected UserService $service;

    /**
     * UserController constructor
     *
     * @inheritdoc
     
     * @throws InvalidConfigException
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->service = Yii::$container->get(UserService::class);
    }

    /**
     * Endpoint for user registration
     *
     * @return array
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException|Exception
     
     */
    public function actionCreate(): array
    {
        // TODO: RBAC permission control - guest + admin

        $params = Yii::$app->getRequest()->getBodyParams();
        Yii::$app->response->statusCode = 201;
        // Creates a new user and saves it in the database
        return $this->service->create($params);
    }

    /**
     * Endpoint for user update
     *
     * @param $id
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     
     */
    public function actionUpdate($id): array
    {
        // TODO: RBAC permission control - guest + admin

        $params = Yii::$app->getRequest()->getBodyParams();

        // Creates a new user and saves it in the database
        return $this->service->update($id, $params);
    }

    /**
     * Endpoint for user deletion
     *
     * @param $id
     * @return array
     * @throws Exception
     * @throws ServerErrorHttpException
     
     */
    public function actionDelete($id): array
    {
        // TODO: RBAC permission control - guest + admin

        Yii::$app->response->statusCode = 204;

        // Creates a new user and saves it in the database
        return $this->service->delete($id);

    }

    /**
     * Endpoint for reading specific user
     *
     * @param $id
     * @return array
     * @throws Exception
     
     */
    public function actionView($id): array
    {
        // TODO: RBAC permission control - guest + admin

        // Creates a new user and saves it in the database
        return $this->service->view($id);
    }
}