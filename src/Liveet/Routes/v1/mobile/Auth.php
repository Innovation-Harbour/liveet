<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\AuthController;
use Liveet\Middlewares\AuthenticationMiddleware;


isset($mobileGroup) && $mobileGroup->group(
    "/auth",
    function (RouteCollectorProxy $authGroup) {

        $authGroup->post(
            "/register",
            AuthController::class . ":Register"
        );

        $adminGroup->post(
            "/login",
            AuthController::class . ":Login"
        );
    }
);
