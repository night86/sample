<?php

namespace console\models\tasks;

use common\models\BaseModel;
use MongoDB\BSON\ObjectId;

/**
 * Class TaskMaintainerTask
 * @package console\models\tasks
 */
class TaskMaintainerTask extends BaseTask
{
    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        self::deleteAll(
            [
                "status" => self::STATUS_FINISHED,
                "scheduled_at" => [
                    '$lt' => time() - BaseModel::getConfig('finishedTaskDeletionAgeLimit')
                ]
            ]
        );

        $task = new BaseTask([
            '_id' => new ObjectId(),
            'status' => parent::STATUS_NEW,
            'class_name' => self::class,
        ]);
        $task->save();
    }
}