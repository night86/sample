<?php

namespace common\components\model;

use common\models\BaseModel;
use MongoDB\BSON\ObjectId;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;

/**
 * Class ModelTrait
 * @package common\components\model
 */
trait ModelTrait
{
    /**
     * Get a specific model object using the id.
     *
     * @param string|ObjectID $id
     * @return mixed
     * @throws NotFoundHttpException
     
     */
    public static function getOneById($id)
    {
        $className = get_called_class();

        if (empty($id)) {
            Yii::error("{$className}({$id}) not found");
            throw new NotFoundHttpException("{$className}({$id}) not found.");
        }

        /** @var BaseModel $className */
        $model = $className::findOne(['_id' => $id instanceof ObjectId ? $id : new ObjectID($id)]);

        if (!$model instanceof $className) {
            Yii::error("{$className}({$id}) not found");
            throw new NotFoundHttpException("{$className}({$id}) not found.");
        }

        return $model;
    }

    /**
     * Returns with an object, which was filtered by one or multiple conditions.
     *
     
     * @param array $filter
     * @return mixed
     * @throws NotFoundHttpException
     */
    public static function getOneByFilter(array $filter = [])
    {
        $className = get_called_class();

        if (!is_array($filter)) {
            Yii::error("{$className}(" . VarDumper::dumpAsString($filter) . ') not found');
            throw new InvalidArgumentException(Yii::t('app', "Invalid {$className} parameter."));
        }

        /** @var BaseModel $className */
        $model = $className::findOne($filter);

        if (!$model instanceof $className) {
            Yii::error("{$className}(" . VarDumper::dumpAsString($filter) . ') not found');
            throw new NotFoundHttpException(Yii::t('app', "{$className} not found."));
        }

        return $model;
    }
}