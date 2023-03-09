<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\components\model\search;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Connection;
use yii\mongodb\Exception;

/**
 * Class MongoDBAggregationDataProvider
 * @package common\components\model\search
 *
 * @property string $collectionName
 * @property array $pipeline
 * @property string|callable $key
 * @property ActiveRecord $model
 * @property array $matchStage
 * @property array $sort
 */
class MongoDBAggregationDataProvider extends BaseDataProvider implements GridServiceInterface
{
    use GridServiceTrait;

    /**
     * Current grid page parameter
     *
     * @string
     */
    public const GRID_CURRENT_PAGE_PARAM = 'page';

    /**
     * Current grid page size parameter
     *
     * @string
     */
    public const GRID_PAGE_SIZE_PARAM = 'page_size';

    /**
     * Grid sort direction parameter
     *
     * @string
     */
    public const GRID_SORT_PARAM = 'sort';

    /**
     * Filters to be applied on the search
     *
     * @string
     */
    public const GRID_FILTER_PARAM = 'filters';

    /**
     * Free text search parameter
     *
     * @string
     */
    public const GRID_FREETEXT_SEARCH_PARAM = 'freetext';

    public const ARRAY_PREPEND = 'prepend';
    public const ARRAY_APPEND = 'append';
    public const ARRAY_MERGE = 'merge';

    /**
     * The target collection name
     *
     * @var string
     */
    public string $collectionName;

    /**
     * Aggregation Pipeline
     * For more information see https://docs.mongodb.com/manual/core/aggregation-pipeline/
     *
     * @var array
     */
    public array $pipeline = [];

    /**
     * @var string|callable name of the key column or a callable returning it
     */
    public $key;

    /**
     * Model class which will be use for format output array
     *
     * @var ActiveRecord
     */
    public ActiveRecord $model;

    /**
     * The filter stage of the pipeline
     *
     * @var array
     */
    protected array $matchStage = [];

    /**
     * The sort stage of the pipeline
     *
     * @var array
     */
    protected array $sort;

    /**
     * Sets the sort definition for this data provider.
     *
     * @param mixed $value the sort definition to be used by this data provider.
     * @return void
     
     */
    public function setSort($value): void
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Only [field => sort direction] array is allowed.');

        }

        $this->sort = [key($value) => current($value) === SORT_ASC ? 1 : -1];
    }

    /**
     * Apply provided filter
     *
     * @param array $filterData passed by client
     * @param array $propertyMap attribute aliases
     * @throws InvalidConfigException
     
     */
    public function filter(array $filterData, array $propertyMap): void
    {
        $pipeline = [$filterData[GridService::LOGIC_KEY]];

        $this->filterInside($filterData, $propertyMap, $pipeline);

        Yii::info('Nested filter ' . VarDumper::dumpAsString($pipeline));

        $this->matchStage = ['$match' => $pipeline];
    }

    /**
     * Translate kendo datasource filter operators to datasource operators
     *
     * @return OperatorModel
     
     */
    public function getOperators(): OperatorModel
    {
        $model = new OperatorModel();
        // TODO: implement other comparison functions
        $model->eq = static function ($a, $b) {
            return [$a => $b];
        };
        $model->neq = static function ($a, $b) {
            return [$a => ['$ne' => $b]];
        };
        $model->all = static function ($a, $b) {
            return [$a => ['$all' => $b]];
        };
        $model->in = static function ($a, $b) {
            return [$a => ['$in' => $b]];
        };
        $model->lt = static function ($a, $b) {
            return [$a => ['$lt' => $b]];
        };
        $model->lte = static function ($a, $b) {
            return [$a => ['$lte' => $b]];
        };
        $model->gt = static function ($a, $b) {
            return [$a => ['$gt' => $b]];
        };
        $model->gte = static function ($a, $b) {
            return [$a => ['$gte' => $b]];
        };
        $model->startswith = static function ($a, $b) {
            return [$a => new Regex('^' . $b, 'i')];
        };
        $model->endswith = static function ($a, $b) {
            return [$a => new Regex($b . '$', 'i')];
        };
        $model->contains = static function ($a, $b) {
            return [$a => new Regex($b, 'i')];
        };
        $model->isnull = static function ($a) {
            return [$a => null];
        };
        $model->isnotnull = static function ($a) {
            return [$a => ['$ne' => null]];
        };

        return $model;
    }

    /**
     * Add additional filter criteria to the query
     *
     * @param array $criteria
     * @param string $position
     * @return void
     
     */
    public function addCriteria(array $criteria, string $position = MongoDBAggregationDataProvider::ARRAY_APPEND): void
    {
        switch ($position) {
            case static::ARRAY_APPEND:
                $this->pipeline[] = $criteria;
                break;
            case static::ARRAY_PREPEND:
                array_unshift($this->pipeline, $criteria);
                break;
            case static::ARRAY_MERGE:
                $this->pipeline = ArrayHelper::merge($this->pipeline, $criteria);
                break;
            default:
                break;
        }
    }

    /**
     * Prepares the data models that will be made available in the current page.
     *
     * @return array the available data models
     * @throws InvalidConfigException
     * @throws Exception
     
     */
    protected function prepareModels(): array
    {
        if (empty($this->collectionName)) {
            throw new InvalidConfigException('The "collectionName" property must be set.');
        }

        if (!empty($this->matchStage)) {
            $this->pipeline[] = $this->matchStage;
        }

        $pagination = $this->getPagination();

        if (!empty($this->sort)) {
            $this->pipeline[] = ['$sort' => $this->sort];
        }

        if ($pagination !== false) {
            $pagination->totalCount = $this->getTotalCount();

            $this->pipeline[] = ['$skip' => $pagination->getOffset()];
            $this->pipeline[] = ['$limit' => $pagination->getLimit()];
        }

        Yii::info(static::class . ' prepareModels() pipeline ' . VarDumper::dumpAsString($this->pipeline));

        /** @var Connection $mongo */
        /** @noinspection PhpUndefinedFieldInspection */
        $mongo = Yii::$app->mongodb;
        $result = $mongo->getCollection($this->collectionName)->aggregate($this->pipeline);

        return $this->formatOutput($result);
    }

    /**
     * This function is formatting MongoDB result to corresponding array.
     *
     * @param array $result
     * @param boolean $nested the process is in a recursive call or not
     * @return array
     
     */
    protected function formatOutput(array $result, bool $nested = false): array
    {
        foreach ($result as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            foreach ($item as $field => $value) {
                if ($value instanceof ObjectID) {
                    $result[$index][$field] = (string)$value;
                }

                if (is_array($value) && isset($value[0])) {
                    // for the nested models
                    $result[$index][$field] = $this->formatOutput($value, true);
                }
            }
        }

        if ($nested === false && method_exists($this->model, 'prepareGridModels')) {
            return $this->model->prepareGridModels($result);
        }

        return $result;
    }

    /**
     * Prepares the keys associated with the currently available data models.
     *
     * @param array $models the available data models
     * @return array the keys
     
     */
    protected function prepareKeys($models): array
    {
        $keys = [];
        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        }

        return array_keys($models);
    }

    /**
     * Returns a value indicating the total number of data models in this data provider.
     *
     * @return int total number of data models in this data provider.
     * @throws Exception
     
     */
    protected function prepareTotalCount(): int
    {
        Yii::info(
            static::class . ' prepareTotalCount() pipeline ' . VarDumper::dumpAsString($this->pipeline)
        );

        /** @var Connection $mongo */
        /** @noinspection PhpUndefinedFieldInspection */
        $mongo = Yii::$app->mongodb;
        $result = $mongo
            ->getCollection($this->collectionName)
            ->aggregate(is_null($this->pipeline) ? [] : $this->pipeline);

        return count($result);
    }
}