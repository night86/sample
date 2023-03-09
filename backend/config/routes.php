<?php
/**
 * 
 * Copyright © 2022. All rights reserved.
 */

return [
    // OAUTH2 ROUTES
    'OPTIONS v1/oauth2/<action:[\w\-]+>' => 'oauth2/authentication/options',
    'POST v1/oauth2/<action:\w+>' => 'oauth2/authentication/<action>',

    // HEALTH ROUTES
    'GET v1/health/check'     => 'v1/health/check',

    // GENERAL REST ROUTES
    'GET     <module:[\w\-]+>/<controller:[\w\-]+>'      => '<module>/<controller>/index',
    'GET     <module:[\w\-]+>/<controller:[\w\-]+>/<id>' => '<module>/<controller>/view',
    'POST    <module:[\w\-]+>/<controller:[\w\-]+>'      => '<module>/<controller>/create',
    'PUT     <module:[\w\-]+>/<controller:[\w\-]+>/<id>' => '<module>/<controller>/update',
    'DELETE  <module:[\w\-]+>/<controller:[\w\-]+>/<id>' => '<module>/<controller>/delete',

    // GENERAL OPTIONS ROUTES
    'OPTIONS <module:[\w\-]+>/<controller:[\w\-]+>'               => '<module>/<controller>/options',
    'OPTIONS <module:[\w\-]+>/<controller:[\w\-]+>/<id>'          => '<module>/<controller>/options',
    'OPTIONS <module:[\w\-]+>/<controller:[\w\-]+>/<action>/<id>' => '<module>/<controller>/options',
];