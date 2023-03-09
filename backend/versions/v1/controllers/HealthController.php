<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace backend\versions\v1\controllers;

use backend\versions\common\controllers\SecureRestController;

/**
 * Class HealthController
 * @package backend\versions\v1\controllers
 */
class HealthController extends SecureRestController
{
    /**
     * Allowed actions without authentication for users in current controller (CompositeAuth->$expect)
     *
     * @var array
     */
    protected array $noAuthAccessActions = [
        'check'
    ];

    public array $collectionOptions = [];
    public array $resourceOptions = ['GET'];

    /**
     * End point for health check.
     *
     * @return array
     
     */
    public function actionCheck(): array
    {
        return [];
    }
}