<?php

namespace common\components\request;

use Exception;
use Yii;
use yii\helpers\StringHelper;
use yii\web\Request;

/**
 * Class WebResponse
 * @package common\components\request
 */
class WebRequest extends Request
{
    /**
     * Contains the request ID
     *
     * @var string
     */
    protected string $requestId;

    /**
     * Contains the serializer object
     *
     * @var Serializer|object
     */
    protected Serializer $serializer;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->requestId = StringHelper::base64UrlEncode(random_bytes(6));

        /** @var Serializer $serializer */
        $this->serializer = Yii::$container->get(Serializer::class);
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        $params = parent::getQueryParams();
        $this->serializer->serialize($params);

        return $params;
    }

    /**
     * @inheritDoc
     */
    public function getBodyParams()
    {
        $params = parent::getBodyParams();
        $this->serializer->serialize($params);

        return $params;
    }

    /**
     * Returns with the user's original IP address.
     *
     * @return string
     
     */
    public function getUserIP(): string
    {
        $ip = '127.0.0.1';

        $userIP = parent::getUserIP();
        if ($userIP !== null) {
            $ip = $userIP;
        }

        // Returns with all the request headers
        $headers = $this->getRequestHeaders();
        $forwardedIP = $headers['X-Forwarded-For'] ?? null;
        if ($forwardedIP !== null) {
            $ip = $forwardedIP;
        }

        return $ip;
    }

    /**
     * Returns with all the request headers
     *
     * @return array|false
     
     */
    public function getRequestHeaders()
    {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }

        $headerArguments = [];
        $rxHttp = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rxHttp, $key)) {
                $headerKey = preg_replace($rxHttp, '', $key);
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rxMatches = explode('_', $headerKey);
                if (!empty($rxMatches) && strlen($headerKey) > 2) {
                    foreach ($rxMatches as $akKey => $akVal) {
                        $rxMatches[$akKey] = ucfirst($akVal);
                    }
                    $headerKey = implode('-', $rxMatches);
                }
                $headerArguments[$headerKey] = $val;
            }
        }

        return $headerArguments;
    }
}
