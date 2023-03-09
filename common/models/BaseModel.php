<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\models;

use common\components\model\ModelTrait;
use MongoDB\BSON\ObjectId;
use Yii;
use yii\helpers\VarDumper;
use yii\mongodb\ActiveRecord;
use yii\web\ServerErrorHttpException;

/**
 * Class BaseModel
 * @package common\models
 *
 * @property ObjectId $_id
 * @property string $collectionName
 */
class BaseModel extends ActiveRecord
{
    use ModelTrait;

    /**
     * Collection name of the class
     *
     * @var string
     */
    protected static string $collectionName;

    /**
     * Returns with the collection name of the class.
     *
     
     * @return string
     */
    public static function collectionName(): string
    {
        return static::$collectionName;
    }

    /**
     * Performs a validated save.
     *
     
     * @param boolean $validate
     * @param array|null $fields
     * @return void
     * @throws ServerErrorHttpException
     */
    public function validatedSave(bool $validate = true, array $fields = null): void
    {
        $className = get_called_class();

        if (!$this->save($validate, $fields)) {
            $errors = $this->getErrorSummary(false);
            Yii::warning("{$className}({$this->_id}) saving error  " . VarDumper::dumpAsString($this->getErrors()));
            throw new ServerErrorHttpException(Yii::t('app', 'Failed to save : ' . '<br/>' . $errors[0]));
        }
    }

    /**
     * Returns with a specific single level configuration value.
     *
     
     * @param string $key
     * @return int
     */
    public static function getConfig(string $key): int
    {
        return Yii::$app->params[$key] ?? '';
    }
}