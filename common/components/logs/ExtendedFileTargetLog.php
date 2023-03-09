<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace common\components\logs;

use common\components\request\ConsoleRequest;
use common\components\request\WebRequest;
use common\models\User;
use Yii;
use yii\log\FileTarget;

/**
 * Class ExtendedFileTargetLog
 * @package common\components\logs
 */
class ExtendedFileTargetLog extends FileTarget
{
    /**
     * @inheritDoc
     */
    public function getMessagePrefix($message)
    {
        // If prefix is set in config use it and add request id
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }

        /** @var WebRequest|ConsoleRequest $request */
        $request = Yii::$app->request ?? null;
        $requestID = $request->hasMethod('getRequestId') ? $request->getRequestId() : '-';

        if ($request->isConsoleRequest) {
            $controller = isset(Yii::$app->controller) ? Yii::$app->controller->id : '-';
            $action = isset(Yii::$app->controller->action) ? Yii::$app->controller->action->id : '-';

            return "[{$controller}/{$action}][{$requestID}]";
        }

        /** @var User $user */
        $userID = isset(Yii::$app->user) ? Yii::$app->user->id : '-';
        $ip = $request ? $request->getUserIP() : '-';
        $url = $request ? $request->url : '-';
        $method = $request ? $request->method : '-';

        return "[{$ip}][{$userID}][{$method}][{$url}][{$requestID}]";
    }
}