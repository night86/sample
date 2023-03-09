<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace tests\functional;

use common\models\User;
use FunctionalTester;
use MongoDB\BSON\ObjectId;

/**
 * Class UserRegistrationCest
 * @package tests\functional
 *
 * @property array $config
 * @property string $apiPrefix
 */
class UserRegistrationCest
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
     * Testing the user registration process
     *
     * @param FunctionalTester $I
     * @return void
     * @throws \Exception
     
     */
    public function registerUser(FunctionalTester $I): void
    {
        $I->am('a guest');
        $I->wantTo('register');

        $parameters = [
            'email' => 'test@email.com'
        ];

        $I->sendPOST("{$this->apiPrefix}user", $parameters);
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $user = $I->grabFromJsonResponse();

        $I->assertTrue(isset($user['_id']));
        $I->assertTrue(isset($user['status']));
        $I->assertTrue(isset($user['email']));
        $I->assertFalse(isset($user['password_hash']));
        $I->assertFalse(isset($user['created_at']));
        $I->assertFalse(isset($user['updated_at']));
        $I->assertEquals(User::STATUS_PENDING, $user['status']);
        $I->assertEquals($parameters['email'], $user['email']);

        $user = $I->grabFromCollection(User::collectionName(), [
            '_id' => new ObjectId($user['_id'])
        ]);
        $I->assertNotEmpty($user);
        $I->assertNotEmpty($user['password_hash']);
    }
}