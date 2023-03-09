<?php

namespace tests\unit;

use UnitTester;

/**
 * Class QueueManagerCest
 * @package tests\unit
 */
class QueueManagerCest
{
    /**
     * Queue manager for tasks
     *
     * @var QueueManager
     */
    protected QueueManager $queue;

    public function _before(UnitTester $I)
    {
        $this->queue = new QueueManager();
    }

    /**
     * Testing the queue manager's functionality
     *
     * @param UnitTester $I
     * @return void
     
     */
    public function processQueue(UnitTester $I): void
    {
        $I->am('a terminal');
        $I->wantToTest('the basic functionality of the queue manager');

        $I->grabFromCollection('task', [
            'status' => BaseTask::STATUS_NEW
        ]);

        $this->queue->processTasks();

        $I->assertNotEmpty();
    }
}
