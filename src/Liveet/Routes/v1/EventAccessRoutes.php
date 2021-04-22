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
            "/get/accesses/group/{event_id}[/{page}[/{limit}]]",
            EventAccessController::class . ":getEventAccessGroup"
        );

        $eventGroup->get(
            "/get/accesses[/{event_ticket_id}[/{page}[/{limit}]]]",
            EventAccessController::class . ":getEventAccesses"
        );

        $eventGroup->get(
            "/get/access/{event_access_id}",
            EventAccessController::class . ":getEventAccessByPK"
        );

        $eventGroup->put(
            "/assign/access/{event_access_id}",
            EventAccessController::class . ":assignEventAccessByPK"
        );

        $eventGroup->delete(
            "/delete/access/{event_access_id}",
            EventAccessController::class . ":deleteEventAccessByPK"
        );

        $eventGroup->delete(
            "/delete/accesses",
            EventAccessController::class . ":deleteEventAccessByPKs"
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
            "/get/accesses[/{event_ticket_id}[/{page}[/{limit}]]]",
            EventAccessController::class . ":getOrganiserEventAccesses"
        );

        $eventGroup->put(
            "/assign/access/{event_access_id}",
            EventAccessController::class . ":assignOrganiserEventAccessByPK"
        );
    }
);
