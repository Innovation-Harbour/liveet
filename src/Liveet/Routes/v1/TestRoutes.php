<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\TestController;
use Liveet\Middlewares\AuthenticationMiddleware;


/**
 * Test priviledged
 */
isset($v1Group) && $v1Group->group(
    "/tests",
    function (RouteCollectorProxy $testGroup) {
    }
)
    // ->addMiddleware(new AuthenticationMiddleware((new TestModel())))
    ;

/**
 * No auth
 * Test priviledged
 */
isset($v1Group) && $v1Group->group(
    "/tests",
    function (RouteCollectorProxy $testGroup) {
    }
);
