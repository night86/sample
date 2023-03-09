<?php

defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('YII_APP_BASE_PATH') || define('YII_APP_BASE_PATH', dirname(__DIR__));

require_once YII_APP_BASE_PATH . '/vendor/autoload.php';
require_once YII_APP_BASE_PATH . '/vendor/yiisoft/yii2/Yii.php';

/**
 * Application configuration for backend acceptance tests
 */
$options = yii\helpers\ArrayHelper::merge(
    require YII_APP_BASE_PATH . '/common/config/main.php',
    require YII_APP_BASE_PATH . '/common/config/main-local.php',
    require YII_APP_BASE_PATH . '/backend/config/main.php',
    require YII_APP_BASE_PATH . '/backend/config/main-local.php',
    require dirname(__DIR__) . '/config/config.php',
    require dirname(__DIR__) . '/config/config-unit-local.php',
    require dirname(__DIR__) . '/config/params.php',
    []
);

if (isset($options['components']['cache']['redis'])) {
    unset($options['components']['cache']['redis']);
}

return $options;
