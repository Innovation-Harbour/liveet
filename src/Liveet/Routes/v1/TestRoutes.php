<?php

use Liveet\Controllers\LocationController;
use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\TestController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\AdminUserModel;
use Liveet\Models\TestModel;


/**
 * Test priviledged
 */
isset($v1Group) && $v1Group->group(
    '/tests',
    function (RouteCollectorProxy $testGroup) {
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new TestModel())));

/**
 * No auth
 * Test priviledged
 */
isset($v1Group) && $v1Group->group(
    '/tests',
    function (RouteCollectorProxy $testGroup) {
    }
);
