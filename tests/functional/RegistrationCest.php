<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

use FunctionalTester;
use MongoDB\BSON\ObjectId;

/**
 * Class RegistrationCest
 */
class RegistrationCest
{
    /**
     * Testing the functional tester configuration
     *
     * @param FunctionalTester $I
     * @return void
     
     */
    public function testFunction(FunctionalTester $I): void
    {
        $I->am('a tester');
        $I->wantToTest('the functionality of the functionality test');

        $id = new ObjectId();
        $I->haveInCollection('task', ['_id' => $id]);

        $I->seeInCollection('task', ['_id' => $id]);

        $I->sendPOST('user', []);
    }
}