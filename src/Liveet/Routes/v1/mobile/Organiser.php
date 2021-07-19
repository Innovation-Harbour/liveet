<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\OrganiserController;


isset($mobileGroup) && $mobileGroup->group(
    "/organiser",
    function (RouteCollectorProxy $organiserGroup) {

        $organiserGroup->post(
            "/login",
            OrganiserController::class . ":loginOrganiser"
        );

        $organiserGroup->post(
            "/verify/{event_id}",
            OrganiserController::class . ":verifyUser"
        );

        $organiserGroup->get(
            "/getevents",
            AuthController::class . ":getOrganiserEvent"
        );
    }
);
