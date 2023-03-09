<?php

namespace tests\unit;

use console\models\tasks\BaseTask;
use console\models\tasks\EmailSenderTask;
use MongoDB\BSON\ObjectId;
use UnitTester;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

/**
 * Class EmailSenderTaskCest
 * @package tests\unit
 *
 * @property EmailSenderTask $task
 */
class EmailSenderTaskCest
{
    /**
     * Task maintainer task
     *
     * @var EmailSenderTask
     */
    protected EmailSenderTask $task;

    public function _before(UnitTester $I)
    {
        $config = require __DIR__ . '/../config/config.php';

        try {
            Yii::$app->set('mailer', $config['components']['mailer']);
        } catch (InvalidConfigException $e) {
            codecept_debug('Error: ' . $e->getMessage());
        }

        $this->task = new EmailSenderTask();
    }

    /**
     * Testing the email sender
     *
     * @param UnitTester $I
     * @return void
     */
    public function processEmailSender(UnitTester $I): void
    {
        $I->am('a terminal');
        $I->wantToTest('the basic functionality of the email sender');

        // this will add one entry to the database
        $I->haveInCollection(EmailSenderTask::collectionName(), [
            '_id' => new ObjectId(),
            'class_name' => EmailSenderTask::class,
            'status' => BaseTask::STATUS_NEW,
            'created_at' => time()
        ]);

        // this will execute the task
        $this->task->execute();

        $task = $I->grabFromCollection(EmailSenderTask::collectionName(), [
            'class_name' => EmailSenderTask::class,
            'status' => BaseTask::STATUS_FINISHED,
        ]);

        $I->assertNotEmpty($task);
        $I->assertNotTrue(isset($task['result_code']));
        codecept_debug('yo:'. VarDumper::dumpAsString($task));
        $I->assertEquals(EmailSenderTask::RESULT_CODE_SUCCESS, $task['result_code']);
        $I->assertTrue($task['result_details']);
        $I->assertEmpty($task['result_details']);
    }
}
