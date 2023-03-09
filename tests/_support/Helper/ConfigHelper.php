<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace Helper;

use Codeception\Module;

/**
 * Class ConfigHelper
 * @package Helper
 */
class ConfigHelper extends Module
{
    /**
     * Returns with a specific configuration key value
     *
     * @param $key
     * @return null
     */
    public function getConfig($key)
    {
        return $this->config[$key] ?? null;
    }
}