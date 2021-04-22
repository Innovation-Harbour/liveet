<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/event",
            EventController::class . ":createEvent"
        );

        $eventGroup->get(
            "/get/events[/{event_id}[/{event_code}[/{event_type}[/{payment_type}[/{organiser_id}[/{page}[/{limit}]]]]]]]",
            EventController::class . ":getEvents"
        );

        $eventGroup->get(
            "/get/event/{event_id}",
            EventController::class . ":getEventByPK"
        );

        $eventGroup->put(
            "/update/event/{event_id}",
            EventController::class . ":updateEventByPK"
        );

        $eventGroup->delete(
            "/delete/event/{event_id}",
            EventController::class . ":deleteEventByPK"
        );

        $eventGroup->put(
            "/enable/event/{event_id}",
            EventController::class . ":enableEventByPk"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->get(
            "/get/events[/{event_id}[/{event_code}[/{event_type}[/{payment_type}[/{page}[/{limit}]]]]]]",
            EventController::class . ":getOrganiserEvents"
        );
    }
);
