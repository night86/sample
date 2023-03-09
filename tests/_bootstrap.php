<?php

defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('YII_ENV') || define('YII_ENV', 'test');

defined('YII_APP_BASE_PATH') || define('YII_APP_BASE_PATH', dirname(__DIR__));

require_once YII_APP_BASE_PATH . '/vendor/autoload.php';
require_once YII_APP_BASE_PATH . '/vendor/yiisoft/yii2/Yii.php';
require_once YII_APP_BASE_PATH . '/common/config/bootstrap.php';
require_once YII_APP_BASE_PATH . '/backend/config/bootstrap.php';

Yii::setAlias('@tests', dirname(__DIR__));
Yii::setAlias('backend', dirname(__DIR__) . '/backend');
Yii::setAlias('common', dirname(__DIR__) . '/common');
Yii::setAlias('console', dirname(__DIR__) . '/console');
