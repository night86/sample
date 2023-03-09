<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace tests\functional;

use FunctionalTester;

/**
 * Class HealthCest
 * @package tests\functional
 *
 * @property array $config
 * @property string $apiPrefix
 */
class HealthCheckCest
{
    /**
     * Contains API configuration
     *
     * @var array
     */
    protected array $config;

    /**
     * Contains API module prefix
     *
     * @var string
     */
    protected string $apiPrefix;

    /**
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $this->config['backend'] = $I->getConfig('backend');

        $this->apiPrefix = $this->config['backend']['api_prefix'];
    }

    /**
     * Testing the health checker end point
     *
     * @param FunctionalTester $I
     * @return void
     
     */
    public function healthCheck(FunctionalTester $I): void
    {
        $I->sendGET("{$this->apiPrefix}health/check");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }
}