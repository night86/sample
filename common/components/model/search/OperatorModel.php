<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\components\model\search;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Class OperatorModel
 * @package common\components\model\search
 */
class OperatorModel
{
    public $eq;
    public $neq;
    public $in;
    public $all;
    public $isnull;
    public $isnotnull;
    public $lt;
    public $lte;
    public $gt;
    public $gte;
    public $startswith;
    public $endswith;
    public $contains;
    public $doesnotcontain;
    public $isempty;
    public $isnotempty;

    /**
     * @param $operator
     * @param $field
     * @param $value
     * @return mixed
     * @throws InvalidConfigException
     */
    public function filter($operator, $field, $value)
    {
        if (property_exists($this, $operator) && is_callable($this->{$operator})) {
            return call_user_func_array($this->{$operator}, [$field, $value]);
        }

        throw new InvalidConfigException(Yii::t(
            'app',
            'Operator {operator} not found or not callable.',
            compact('operator')
        ));
    }
}