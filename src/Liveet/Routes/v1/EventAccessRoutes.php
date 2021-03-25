<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventAccessController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/access",
            EventAccessController::class . ":createEventAccess"
        );

        $eventGroup->get(
            "/get/accesss[/{event_id}[/{page}[/{limit}]]]",
            EventAccessController::class . ":getEventAccesss"
        );

        $eventGroup->get(
            "/get/access/{event_access_id}",
            EventAccessController::class . ":getEventAccessByPK"
        );

        $eventGroup->put(
            "/update/access/{event_access_id}",
            EventAccessController::class . ":updateEventAccessByPK"
        );

        $eventGroup->delete(
            "/delete/access/{event_access_id}",
            EventAccessController::class . ":deleteEventAccessByPK"
        );

        $eventGroup->put(
            "/enable/access/{event_id}",
            EventAccessController::class . ":enableEventAccessByPk"
        );
    }
);

/**
 * Admin User Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->get(
            "/get/accesss[/{event_id}[/{event_code}[/{event_type}[/{payment_type}[/{page}[/{limit}]]]]]]",
            EventAccessController::class . ":getOrganiserEventAccesss"
        );
    }
);
