<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $adminFeatureGroup) {

        $adminFeatureGroup->post(
            "/create/event",
            EventController::class . ":createEvent"
        );

        $adminFeatureGroup->get(
            "/get/events[/{page}[/{limit}]]",
            EventController::class . ":getEvents"
        );

        $adminFeatureGroup->get(
            "/get/event/{admin_event_id}",
            EventController::class . ":getEventByPK"
        );

        $adminFeatureGroup->put(
            "/update/event/{admin_event_id}",
            EventController::class . ":updateEventByPK"
        );

        $adminFeatureGroup->post(
            "/assign/event",
            EventController::class . ":assignEvent"
        );

        $adminFeatureGroup->get(
            "/get/assigned-events[/{admin_user_id}[/{admin_event_id}[/{page}[/{limit}]]]]",
            EventController::class . ":getAssignedEvents"
        );

        $adminFeatureGroup->put(
            "/update/assigned-event/{admin_event_user_id}",
            EventController::class . ":updateAssignedEventByPK"
        );
    }
);
