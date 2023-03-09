<?php

namespace common\models\search;

/**
 * Interface SearchModelInterface
 * @package common\models\search
 */
interface SearchModelInterface
{
    /**
     * Perform the model search for the front-end grid
     *
     * @param array $params
     * @return mixed
     */
    public function search(array $params);
}