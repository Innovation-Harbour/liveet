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

        $mobileEventGroup->get(
            "/gethistory/{user_id}/{offset}/{limit}",
            EventMobileController::class . ":getUserEventHistory"
        );

        $mobileEventGroup->post(
            "/dofavourite",
            EventMobileController::class . ":DoEventFavourite"
        );

        $mobileEventGroup->post(
            "/doeventrecall",
            EventMobileController::class . ":DoRecallTicket"
        );

        $mobileEventGroup->post(
            "/checkpayment",
            EventMobileController::class . ":doCheckPayment"
        );

        $mobileEventGroup->post(
            "/doeventtransfer",
            EventMobileController::class . ":DoTicketTransfer"
        );

        $mobileEventGroup->post(
            "/geteventfromaccess",
            EventMobileController::class . ":getEventFromAccess"
        );

        $mobileEventGroup->post(
            "/geteventtickets",
            EventMobileController::class . ":GetEventTickets"
        );

        $mobileEventGroup->post(
            "/geteventmetrics",
            EventMobileController::class . ":getEventMetrics"
        );

        $mobileEventGroup->post(
            "/getnuminvitations",
            EventMobileController::class . ":getNumInvitations"
        );

        $mobileEventGroup->post(
            "/doattendevent",
            EventMobileController::class . ":doAttentEvent"
        );
    }
)->addMiddleware(new AuthenticationMiddleware((new UserModel())));
