<?php

namespace console\models\tasks;

use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;

/**
 * @property mixed $_id
 * @property mixed $result_code
 * @property mixed $result_details
 * @property mixed $status
 * @property mixed $tries
 * @property mixed $priority
 * @property mixed $scheduled_at
 * @property mixed $max_retries
 * @property mixed $retry_after
 * @property mixed $class_name
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class BaseTask extends ActiveRecord implements TaskInterface
{
    /**
     * Task status new
     *
     * @const string
     */
    public const STATUS_NEW = 'new';

    /**
     * Task status finished
     *
     * @const string
     */
    public const STATUS_FINISHED = 'finished';

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        // TODO: Implement execute() method.
    }
    
    /**
     * Returns collection name
     *
     * @return string
     */
    public static function collectionName(): string
    {
        return 'task';
    }

    /**
     * @inheritDoc
     */
    public function attributes(): array
    {
        return [
            // Task identifier
            '_id',
            // Task result code
            'result_code',
            // Task result details
            'result_details',
            // Task status
            'status',
            // Current amount of tries
            'tries',
            // Task priority
            'priority',
            // Date of schedule
            'scheduled_at',
            // Maximum amount of retries
            'max_retries',
            // Time before next retry
            'retry_after',
            // Task class name
            'class_name',
            // Task creation date
            'created_at',
            // Task update date
            'updated_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                TimestampBehavior::class
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['result_details', 'class_name'], 'string'],
            [['class_name'], 'required'],
        ];
    }

    public function setResult(string $resultCode, string $resultDetails){

        $this->status = self::STATUS_FINISHED;
        $this->result_code = $resultCode;
        $this->result_details = $resultDetails;

        $this->save();
    }


}