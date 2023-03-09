<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\models\search;

use common\components\model\search\SearchPaginationTrait;
use common\models\User;
use JsonException;
use yii\base\InvalidConfigException;
use yii\web\UnprocessableEntityHttpException;

/**
 * Class UserSearch
 * @package common\models\search
 */
class UserSearch extends User implements SearchModelInterface
{
    use SearchPaginationTrait;

    /**
     * Perform the model search for the front-end grid
     *
     * @param array $params
     * @return mixed
     * @throws InvalidConfigException
     * @throws JsonException
     * @throws UnprocessableEntityHttpException
     
     */
    public function search(array $params)
    {
        // Set the aggregation data provider for mongoDB
        $provider = $this->setAggregationDataProvider(static::collectionName(), $params);

        $filters = [];

        $provider->addCriteria(
            [
                [
                    '$match' => $filters
                ],
                [
                    '$project' => [
                        'status' => 1,
                        'email' => 1,
                        'firstname' => 1,
                        'lastname' => 1,
                    ]
                ]
            ],
        );

        return $provider;
    }
}