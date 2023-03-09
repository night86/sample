<?php

namespace tests\functional;

use FunctionalTester;

/**
 * Class OAuthAuthenticationCest
 * @package tests\functional
 */
class OAuthAuthenticationCest
{
    /**
     * Contains the configuration of the backend
     *
     * @var array
     */
    protected array $config;

    /**
     * Contains the API prefix
     *
     * @var string
     */
    protected string $apiPrefix;

    public function _before(FunctionalTester $I)
    {
        $this->config = $I->getConfig('backend');
        $this->apiPrefix = $this->config['api_prefix'];
    }

    /**
     * Testing the authentication end point on invalid user.
     *
     * @param FunctionalTester $I
     * @return void
     
     */
    public function authenticateInvalidUser(FunctionalTester $I): void
    {
        $I->am('an invalid user');
        $I->wantTo('receive a valid token, while I have no permission');

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->haveHttpHeader('Authorization', $this->config['auth_header']);

        $I->sendOPTIONS("{$this->apiPrefix}oauth2/token");
        $I->seeResponseCodeIs(200);

        $I->sendPOST("{$this->apiPrefix}oauth2/token", [
            'grant_type' => 'password',
            'password' => 'random',
            'username' => 'asdf@asd.asd'
        ]);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson(
            ['name' => 'Unauthorized']
        );
    }

    /**
     * Testing the authentication end point on a valid user, that should be able to obtain a token
     *
     * @param FunctionalTester $I
     * @return void
     
     */
    public function authenticateValidUser(FunctionalTester $I): void
    {
        $I->am('a valid user');
        $I->wantTo('obtain a token');

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->haveHttpHeader('Authorization', $this->config['auth_header']);

        $I->sendOPTIONS("{$this->apiPrefix}oauth2/token");
        $I->seeResponseCodeIs(200);

        $I->sendPOST("{$this->apiPrefix}oauth2/token", [
            'grant_type' => 'password',
            'password' => 'testpass',
            'username' => 'asdf@asd.asd'
        ]);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
    }
}