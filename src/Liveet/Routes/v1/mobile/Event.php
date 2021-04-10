<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\Mobile\EventMobileController;
use Liveet\Middlewares\AuthenticationMiddleware;


isset($mobileGroup) && $mobileGroup->group(
    "",
    function (RouteCollectorProxy $mobileEventGroup) {

        $mobileEventGroup->get(
            "/getevent",
            EventMobileController::class . ":GetEvents"
        );
    }
);
