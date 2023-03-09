<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\components\model\search;

use yii\helpers\VarDumper;
use yii\web\UnprocessableEntityHttpException;

/**
 * Class SortModel
 * @package common\components\model\search
 *
 * @property string $field
 * @property int $direction
 */
class SortModel
{
    /**
     * Field that sort performed to
     *
     * @var string
     */
    protected string $field = '';

    /**
     * Sort direction
     *
     * @var int
     */
    protected int $direction = SORT_ASC;

    /**
     * SortModel constructor.
     * @param $sortObject
     * @throws UnprocessableEntityHttpException
     */
    public function __construct($sortObject)
    {
        try {
            $sortObject = json_decode($sortObject, false, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            \Yii::error('Unprocessable json input ' . VarDumper::dumpAsString($sortObject));
            throw new UnprocessableEntityHttpException(\Yii::t('app', "Wrong sort parameter format"));
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Yii::error('Unprocessable json input ' . VarDumper::dumpAsString($sortObject));
            throw new UnprocessableEntityHttpException(\Yii::t('app', "Wrong sort parameter format"));
        }

        if (!property_exists($sortObject, 'field')) {
            throw new UnprocessableEntityHttpException(\Yii::t('app', "Missing sort parameter 'field'"));
        }

        if (!property_exists($sortObject, 'dir')) {
            throw new UnprocessableEntityHttpException(\Yii::t('app', "Missing sort parameter 'dir'"));
        }

        if (!in_array($sortObject->dir, ['asc', 'desc'])) {
            throw new UnprocessableEntityHttpException(\Yii::t('app', "Wrong sort parameter 'dir' value"));
        }

        $this->field = $sortObject->field;
        $this->direction = $sortObject->dir === 'asc' ? SORT_ASC : SORT_DESC;
    }

    /**
     * Gets single sort condition
     * Does not support multi-column sorting yet!
     *
     * @return array the columns (and the directions) to be ordered by.
     * @see QueryInterface::addOrderBy()
     */
    public function getOrderParam(): array
    {
        return [$this->field => $this->direction];
    }
}