<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\Mobile\EventController;
use Liveet\Middlewares\AuthenticationMiddleware;


isset($mobileGroup) && $mobileGroup->group(
    "/event",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->get(
            "/getevent/{user_id}/{offset}/{limit}",
            EventMobileController::class . ":GetEvents"
        );
    }
);
