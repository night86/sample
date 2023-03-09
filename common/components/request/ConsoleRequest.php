<?php

namespace common\components\request;

use Exception;
use yii\console\Request;
use yii\helpers\StringHelper;

/**
 * Class ConsoleRequest
 * @package common\components\response
 */
class ConsoleRequest extends Request
{
    protected string $requestId;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->requestId = StringHelper::base64UrlEncode(random_bytes(6));
    }

    /**
     * Gets the request ID from the request
     *
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
