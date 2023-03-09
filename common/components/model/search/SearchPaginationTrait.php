<?php

namespace common\components\model\search;

use http\Exception\InvalidArgumentException;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\UnprocessableEntityHttpException;

/**
 * Trait SearchPaginationTrait
 * @package common\components\model\search
 */
trait SearchPaginationTrait
{
    /**
     * Set the aggregation data provider for mongoDB
     *
     * @param string $collectionName
     * @param array $queryParams
     * @return MongoDBAggregationDataProvider
     * @throws InvalidConfigException
     * @throws UnprocessableEntityHttpException
     * @throws \JsonException
     
     */
    public function setAggregationDataProvider(
        string $collectionName,
        array $queryParams = []
    ): MongoDBAggregationDataProvider {
        $dataProvider = new MongoDBAggregationDataProvider(compact('collectionName'));
        $dataProvider->model = $this;

        // Set the pagination parameters for the data provider
        $this->setPaginationParams($dataProvider, $queryParams);

        if (isset($queryParams[MongoDBAggregationDataProvider::GRID_FILTER_PARAM])) {
            // Prepare filter for the data provider
            $filter = $this->prepareFilter(
                $dataProvider,
                $queryParams[MongoDBAggregationDataProvider::GRID_FILTER_PARAM]
            );

            // Apply provided filter
            $dataProvider->filter($filter, $this->propertyMap());
        }

        // Decides if query params has got free text search request
        if ($this->isFreeTextSearch($queryParams)) {
            // Build free text search query on individual search models
            $this->freeTextSearch(
                $dataProvider,
                $queryParams[MongoDBAggregationDataProvider::GRID_FREETEXT_SEARCH_PARAM]
            );
        }

        if (isset($queryParams[MongoDBAggregationDataProvider::GRID_SORT_PARAM])) {
            // Get the data sort parameters, like field/direction of sort
            $sortData = $this->getSort($queryParams);

            // Sets the sort definition for this data provider.
            $dataProvider->setSort($sortData->getOrderParam());
        }

        return $dataProvider;
    }

    /**
     * Get the data sort parameters, like field/direction of sort
     *
     * @param $queryParams
     * @return SortModel
     * @throws UnprocessableEntityHttpException
     */
    protected function getSort($queryParams): SortModel
    {
        return new SortModel($queryParams[MongoDBAggregationDataProvider::GRID_SORT_PARAM]);
    }

    /**
     * Decides if query params has got free text search request
     *
     * @param array $params
     * @return bool
     
     */
    protected function isFreeTextSearch(array $params): bool
    {
        return isset($params[MongoDBAggregationDataProvider::GRID_FREETEXT_SEARCH_PARAM]) &&
            !empty($params[MongoDBAggregationDataProvider::GRID_FREETEXT_SEARCH_PARAM]);
    }

    /**
     * Translate field names to the stored field format
     *
     * @return array
     
     */
    protected function propertyMap(): array
    {
        return [];
    }

    /**
     * Build free text search query on individual search models
     *
     * @param GridServiceInterface $provider
     * @param string $query
     * @return void
     
     */
    public function freeTextSearch(GridServiceInterface $provider, string $query): void
    {
        // implement on individual search models
    }

    /**
     * Set the pagination parameters for the data provider
     *
     * @param $dataProvider
     * @param $queryParams
     * @return void
     
     */
    protected function setPaginationParams($dataProvider, $queryParams): void
    {
        $paginationParams = isset(
            $queryParams[MongoDBAggregationDataProvider::GRID_PAGE_SIZE_PARAM],
            $queryParams[MongoDBAggregationDataProvider::GRID_CURRENT_PAGE_PARAM]
        );

        if ($paginationParams) {
            $dataProvider->pagination->pageSize = $queryParams[MongoDBAggregationDataProvider::GRID_PAGE_SIZE_PARAM];
            $page = (int)$queryParams[MongoDBAggregationDataProvider::GRID_CURRENT_PAGE_PARAM];
            // paginator page 0 means the first page
            $dataProvider->pagination->page = $page === 0 ? 1 : $page - 1;
        } else {
            // if the grid doesn't pass the required parameters for pagination, the paginator will be disabled
            $dataProvider->pagination = false;
        }
    }

    /**
     * Prepare filter for the data provider
     *
     * @param GridServiceInterface $dataProvider
     * @param string $filter JSON
     * @return array
     * @throws \JsonException
     
     */
    protected function prepareFilter(GridServiceInterface $dataProvider, string $filter): array
    {
        $filter = json_decode(rawurldecode($filter), true, 512, JSON_THROW_ON_ERROR);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(Yii::t('app', 'Unrecognized data format for filter'));
        }

        if ($dataProvider->validateFilter($filter)) {
            // Converts data type of filter values
            return $this->convertFilterDataType($filter);
        }

        throw new InvalidArgumentException(Yii::t('app', 'Required key missing in passed data'));
    }

    /**
     * Converts data type of filter values
     *
     * @param array $filter
     * @return array $filter
     
     */
    protected function convertFilterDataType(array $filter): array
    {
        if (empty($this->paramsValueDataType) || !is_array($this->paramsValueDataType)) {
            return $filter;
        }
        $filterKey = GridService::FILTERS_KEY;
        foreach ($filter[$filterKey] as $k => $item) {
            if (isset($this->paramsValueDataType[$item[GridService::FIELD_KEY]])) {
                $dataType = $this->paramsValueDataType[$item[GridService::FIELD_KEY]];
                switch ($dataType) {
                    case 'int':
                    case 'integer':
                        $filter[$filterKey][$k][GridService::VALUE_KEY] = (int)$item[GridService::VALUE_KEY];
                        break;
                    case 'float':
                        $filter[$filterKey][$k][GridService::VALUE_KEY] = (float)$item[GridService::VALUE_KEY];
                        break;
                    default:
                        break;
                }
            }
        }

        return $filter;
    }
}