<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\Mobile\EventController;
use Liveet\Middlewares\AuthenticationMiddleware;


isset($mobileGroup) && $mobileGroup->group(
    "",
    function (RouteCollectorProxy $mobileEventGroup) {

        $mobileEventGroup->get(
            "/getevent/{user_id}/{offset}/{limit}",
            EventMobileController::class . ":GetEvents"
        );
    }
);
