<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\components\user;

use common\models\User;
use MongoDB\BSON\ObjectId;
use Yii;
use yii\base\Exception;
use yii\web\ServerErrorHttpException;

/**
 * Class UserService
 * @package common\components\user
 */
class UserService
{
    /**
     * Creates a new user and saves it in the database
     *
     * @param array $params
     * @return User
     * @throws ServerErrorHttpException|Exception
     
     */
    public function create(array $params): User
    {
        $user = new User();
        $user->load($params);
        $user->scenario = User::SCENARIO_REGISTRATION;
        $user->email = isset($user->email) ? strtolower($user->email) : null;
        $user->password_hash = Yii::$app->security->generatePasswordHash($params['password']);

        $user->validatedSave();

        return $user;
    }

    /**
     * Creates a new user and saves it in the database
     *
     * @param string $id
     * @param array $params
     * @return User
     * @throws ServerErrorHttpException|Exception
     
     */
    public function update(string $id, array $params): User
    {
        $user = User::getOneById($id);
        $user->load($params);
        $user->validatedSave();

        return $user;
    }

    /**
     * Creates a new user and saves it in the database
     *
     * @param string $id
     * @return User
     * @throws Exception
     
     */
    public function view(string $id): User
    {
        return User::getOneById($id);
    }

    /**
     * Creates a new user and saves it in the database
     *
     * @param string $id
     * @return array
     * @throws ServerErrorHttpException|Exception
     
     */
    public function delete(string $id): array
    {

        $user = User::getOneById($id);
        $user->status = User::STATUS_DELETED;
        $user->validatedSave();

        return [];
    }
}