<?php

namespace tests\unit;

use console\models\tasks\BaseTask;
use console\models\tasks\TaskMaintainerTask;
use MongoDB\BSON\ObjectId;
use UnitTester;

/**
 * Class TaskMaintainerTaskCest
 * @package tests\unit
 */
class TaskMaintainerTaskCest
{
    /**
     * Task maintainer task
     *
     * @var TaskMaintainerTask
     */
    protected TaskMaintainerTask $task;

    public function _before(UnitTester $I)
    {
        $this->task = new TaskMaintainerTask();
    }

    /**
     * Testing the task maintainer
     *
     * @param UnitTester $I
     * @return void
     */
    public function processTaskMaintainer(UnitTester $I): void
    {
        $I->am('a terminal');
        $I->wantToTest('the basic functionality of the task maintainer');

        $now = time();

        $I->comment('adding test task that is ok to be deleted');

        $I->haveInCollection(BaseTask::collectionName(), [
            '_id' => new ObjectId(),
            'status' => BaseTask::STATUS_FINISHED,
            // flag for testing
            'task_to_be_deleted' => true,
            // task scheduled at limit set to something older than the finishedTaskDeletionAgeLimit
            'scheduled_at' => $now - 604800 - 3600
        ]);

        $I->comment('adding object that shouldn\'t be deleted');

        $I->haveInCollection(BaseTask::collectionName(), [
            '_id' => new ObjectId(),
            'status' => BaseTask::STATUS_FINISHED,
            'task_to_be_deleted' => false,
            'scheduled_at' => $now
        ]);

        // this will execute the task
        $this->task->execute();

        $I->comment('Checking the results');

        $I->dontSeeInCollection(BaseTask::collectionName(), [
            'status' => BaseTask::STATUS_FINISHED,
            'task_to_be_deleted' => true
        ]);

        $I->seeInCollection(BaseTask::collectionName(), [
            'status' => BaseTask::STATUS_FINISHED,
            'task_to_be_deleted' => false
        ]);

        // this will check if the task maintenance task created a new copy for further use in the database
        $I->seeInCollection(BaseTask::collectionName(), [
            'status' => BaseTask::STATUS_NEW,
            'class_name' => TaskMaintainerTask::class
        ]);
    }
}
