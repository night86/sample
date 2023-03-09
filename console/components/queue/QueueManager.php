<?php

namespace console\components\queue;

use console\models\tasks\BaseTask;
use yii\base\Component;

/**
 * Class QueueManager
 * @package console\components\queue
 */
class QueueManager extends Component
{

    /**
     * Processes all tasks
     *
     * @return void
     */
    public function processTasks(): void
    {
        $tasksToProcess = BaseTask::findAll(['status' => BaseTask::STATUS_NEW]);

        foreach ($tasksToProcess as $task){

            $taskName = $task->class_name;
            $specificTask = new $taskName;

            foreach ($task->toArray() as $property => $value){

                $specificTask->$property = $value;
            }
            // Executes task
            $specificTask->execute();
            $specificTask->status = BaseTask::STATUS_FINISHED;
            $specificTask->save();
        }
    }
}