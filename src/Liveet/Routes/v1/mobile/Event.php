<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\Mobile\EventMobileController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\UserModel;


isset($mobileGroup) && $mobileGroup->group(
    "",
    function (RouteCollectorProxy $mobileEventGroup) {

        $mobileEventGroup->get(
            "/getevent/{user_id}/{offset}/{limit}",
            EventMobileController::class . ":GetEvents"
        );

        $mobileEventGroup->get(
            "/getfavourite/{user_id}/{offset}/{limit}",
            EventMobileController::class . ":getEventFavourites"
        );

        $mobileEventGroup->post(
            "/dofavourite",
            EventMobileController::class . ":DoEventFavourite"
        );

        $mobileEventGroup->get(
            "/geteventtickets/{event_id}",
            EventMobileController::class . ":GetEventTickets"
        );

        $mobileEventGroup->post(
            "/doattendevent",
            EventMobileController::class . ":doAttentEvent"
        );
    }
)->addMiddleware(new AuthenticationMiddleware((new UserModel())));
