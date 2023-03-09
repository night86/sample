<?php

namespace common\models;

use OAuth2\Storage\UserCredentialsInterface;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $password_hash
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends BaseModel implements IdentityInterface, UserCredentialsInterface
{
    public const STATUS_DELETED = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_INACTIVE = 2;
    public const STATUS_ACTIVE = 3;

    /**
     * Scenario for user registration
     *
     * @var string
     */
    public const SCENARIO_REGISTRATION = 'registration';

    /**
     * Collection name of the class.
     *
     * @var string;
     */
    protected static string $collectionName = 'user';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function scenarios(): array
    {
       $scenarios = parent::scenarios();

       $scenarios[static::SCENARIO_REGISTRATION] = ['email', 'status', 'password_hash'];

       return $scenarios;
    }

    /**
     * Returns the list of all attribute names of the model.
     * This method must be overridden by child classes to define available attributes.
     * Note: primary key attribute "_id" should be always present in returned array.
     * For example:
     *
     * ```php
     * public function attributes()
     * {
     *     return ['_id', 'name', 'address', 'status'];
     * }
     * ```
     *
     * @return array list of attribute names.
     */
    public function attributes(): array
    {
        return [
            '_id',
            'first_name',
            'last_name',
            'email',
            'status',
            'password_hash'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            ['password', 'string', 'min' => 8],
            ['email', 'unique']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Checks the user credentials
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function checkUserCredentials($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user === null) {
            return false;
        }

        return Yii::$app->security->validatePassword($password, $user->password_hash);
    }

    /**
     * Returns with the userID in array if the user exists
     *
     * @param string $email
     * @return array|bool|false
     */
    public function getUserDetails($email)
    {
        $user = static::findByEmail($email);

        if ($user !== null) {
            return ['user_id' => $user->getId()];
        }

        return false;
    }

    /**
     * Finds user by email
     *
     * @param $email
     * @param bool $onlyActive
     * @return User|null
     */
    public static function findByEmail($email, bool $onlyActive = true): User
    {
        $where = ['email' => strtolower($email)];

        if ($onlyActive) {
            $where['status'] = self::STATUS_ACTIVE;
        }

        return static::findOne($where);
    }
}
