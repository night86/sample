<?php

namespace common\components\model\search;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * Trait GridServiceTrait
 * @package common\components\model\search
 *
 * @property array $fields
 */
trait GridServiceTrait
{
    /**
     * The fields being requested. If empty, all fields as specified by Model::fields() will be returned.
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * Sets the response fields
     *
     * @param array $fields
     
     */
    public function setFields(array $fields): void
    {
        // the _id field is required
        $this->fields = ArrayHelper::merge(['_id'], $fields);
    }

    /**
     * Validates the provided filter schema
     *
     * @param array $filter
     * @return bool
     
     */
    public function validateFilter(array $filter): bool
    {
        $gridKey = GridService::FILTERS_KEY;
        if (!array_key_exists($gridKey, $filter)) {
            Yii::info("Key '{$gridKey}' not found in GridService. Passed data: " . VarDumper::dumpAsString($filter));
            return false;
        }

        $result = true;

        foreach ($filter[$gridKey] as $filterData) {
            // Decides if filter is sub-filter array or not
            $result = $this->isFieldFilter($filterData) ?
                // Checks if provided key schema is equal then expected schema
                $this->validateFilterFields($filterData) :
                // Validates the provided filter schema
                $this->validateFilter($filterData);
        }

        return $result;
    }

    /**
     * Checks if provided key schema is equal then expected schema
     *
     * @param array $filters
     * @return bool
     
     */
    protected function validateFilterFields(array $filters): bool
    {
        $result = true;
        $errorKey = '';

        if (!array_key_exists(GridService::OPERATOR_KEY, $filters)) {
            $errorKey = GridService::OPERATOR_KEY;
            $result = false;
        }
        if ($result && !array_key_exists(GridService::FIELD_KEY, $filters)) {
            $errorKey = GridService::FIELD_KEY;
            $result = false;
        }
        if ($result && !array_key_exists(GridService::VALUE_KEY, $filters)) {
            $errorKey = GridService::VALUE_KEY;
            $result = false;
        }

        if (!$result) {
            Yii::info("Key '{$errorKey}' not found in GridService. Params: " . VarDumper::dumpAsString($filters));
        }

        return $result;
    }

    /**
     * Decides if filter is sub-filter array or not
     *
     * @param array $data
     * @return bool
     
     */
    protected function isFieldFilter(array $data): bool
    {
        return isset($data[GridService::OPERATOR_KEY]);
    }

    /**
     * Build nested filter
     *
     * @param array $filter passed by client
     * @param array $propertyMap attribute aliases
     * @param array $criteria where structure holder
     * @return void
     * @throws InvalidConfigException
     
     */
    protected function filterInside(array $filter, array $propertyMap, array &$criteria): void
    {
        $operatorModel = $this->getOperators();

        foreach ($filter[GridService::FILTERS_KEY] as $item) {
            if ($this->isFieldFilter($item)) {
                $field = array_key_exists($item[GridService::FIELD_KEY], $propertyMap) ?
                    $propertyMap[$item[GridService::FIELD_KEY]] :
                    $item[GridService::FIELD_KEY];

                $criteria[] = $operatorModel->filter(
                    $item[GridService::OPERATOR_KEY],
                    $field,
                    $item[GridService::VALUE_KEY]
                );
            } else {
                $subCriteria = [$item[GridService::LOGIC_KEY]];

                // Build nested filter
                $this->filterInside($item, $propertyMap, $subCriteria);

                $criteria[] = $subCriteria;
            }
        }
    }
}