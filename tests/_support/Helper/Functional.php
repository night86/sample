<?php

namespace Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Module\REST;

/**
 * Here you can define custom actions.
 * All public methods declared in helper class will be available in $I
 *
 * Class Functional
 * @package Helper
 *
 * @property REST $api
 */
class Functional extends Module
{
    /**
     * @var REST
     */
    protected REST $api;

    /**
     * @inheritdoc
     * @throws ModuleException
     */
    public function _initialize(): void
    {
        parent::_initialize();

        $this->api = $this->getModule('REST');
    }

    /**
     * Returns data from the current JSON response using [JSONPath](http://goessner.net/articles/JsonPath/) as selector.
     * JsonPath is XPath equivalent for querying Json structures.
     * Try your JsonPath expressions [online](http://jsonpath.curiousconcept.com/).
     *
     * Example:
     *
     * ```
     * // match the first `user.id` in json
     * $firstUserId = $I->grabFromJsonResponse('$..users[0].id');
     * $I->sendPUT('/user', ['id' => $firstUserId, 'name' => 'Tom Bombadil']);
     * ```
     *
     * @param string $jsonFilter
     * @return mixed
     * @throws \Exception
     
     */
    public function grabFromJsonResponse(string $jsonFilter = '')
    {
        $response = $this->api->grabDataFromResponseByJsonPath($jsonFilter);

        $this->api->assertNotEmpty($response);
        $this->api->assertTrue(isset($response[0]));

        return $response[0];
    }
}
