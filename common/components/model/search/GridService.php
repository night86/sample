<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\components\model\search;

/**
 * Class GridService
 * @package common\components\model\search
 */
class GridService
{
    /**
     * "and" or "or" logical operator key name
     *
     * @const string
     */
    public const LOGIC_KEY = 'l';

    /**
     * Filed name key
     *
     * @const string
     */
    public const FIELD_KEY = 'f';

    /**
     * Operator key
     *
     * @const string
     */
    public const OPERATOR_KEY = 'o';

    /**
     * Filter field value key
     *
     * @const string
     */
    public const VALUE_KEY = 'v';

    /**
     * The filter name key
     *
     * @const string
     */
    public const FILTERS_KEY = 'filters';
}