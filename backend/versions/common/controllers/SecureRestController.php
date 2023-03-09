<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

namespace backend\versions\common\controllers;

use yii\rest\Controller;

/**
 * Class SecureRestController
 * @package backend\versions\common\controllers
 *
 * @property array $noAuthAccessActions
 * @property array $optionalAuthAccessActions
 * @property array $collectionOptions
 * @property array $resourceOptions
 * @property ?string $modelClass
 */
class SecureRestController extends Controller
{
    /**
     * Allowed actions without authentication for users in current controller (CompositeAuth->$expect)
     *
     * @var array
     */
    protected array $noAuthAccessActions = [];

    /**
     * Allowed actions for not authenticated users in current controller (CompositeAuth->$optional)
     *
     * @var array
     */
    protected array $optionalAuthAccessActions = [];

    public array $collectionOptions = ['GET', 'POST', 'OPTIONS'];

    public array $resourceOptions = ['GET', 'PUT', 'DELETE', 'OPTIONS'];

    /**
     * Model class of the controller if exists
     *
     * @var string|null
     */
    protected ?string $modelClass = null;
}