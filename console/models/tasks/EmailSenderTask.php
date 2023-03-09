<?php

namespace console\models\tasks;

use console\components\NotificationService;
use MongoDB\BSON\ObjectId;
use Yii;
use yii\base\Exception;
use yii\helpers\VarDumper;

/**
 * Class EmailSenderTask
 * @package console\models\tasks
 */
class EmailSenderTask extends BaseTask
{
    protected NotificationService $notificationService;

    public const RESULT_CODE_SUCCESS = "success";
    public const RESULT_CODE_FAILURE = "failure";

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->notificationService = new NotificationService();
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {

        try {

            $this->notificationService->send('admin@admin.com', [], 'Test email');
            $this->setResult(self::RESULT_CODE_SUCCESS, "");

        } catch(Exception $e) {
            // set the task result to failed here, details of failing can go to the result_details fields
            $this->setResult(self::RESULT_CODE_FAILURE, VarDumper::dumpAsString($e->getMessage()));
        }
    }

    public static function add(string $template, array $parameters = [], array $options = []): void
    {
        $userIP = isset(Yii::$app, Yii::$app->request, Yii::$app->request->userIP)
            ? Yii::$app->getRequest()->getUserIP()
            : null;
        $emailSenderTask = new BaseTask(
            [
                'class_name' => static::class,
                'template' => $template,
                'parameters' => $parameters,
                'userIP' => $userIP,
                'scheduled_at' => $options['scheduled_at'] ?? time(),
                'priority' => (new self)->priority()
            ]
        );
        $emailSenderTask->save();
    }
}