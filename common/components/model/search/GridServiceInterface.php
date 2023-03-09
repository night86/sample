<?php

namespace common\components\model\search;

/**
 * Interface GridServiceInterface
 * @package common\components\model\search
 */
interface GridServiceInterface
{
    /**
     * Translate kendo datasource filter operators to datasource operators
     *
     * @return OperatorModel
     
     */
    public function getOperators(): OperatorModel;

    /**
     * This function is applies the provided filter
     *
     * @param array $filterData passed by client
     * @param array $propertyMap attribute aliases
     
     */
    public function filter(array $filterData, array $propertyMap);

    /**
     * Validates the provided filter schema
     *
     * @param array $filter
     * @return bool
     
     */
    public function validateFilter(array $filter): bool;

    /**
     * Add additional filter criteria to the query
     *
     * @param array $criteria
     * @return void
     
     */
    public function addCriteria(array $criteria): void;
}