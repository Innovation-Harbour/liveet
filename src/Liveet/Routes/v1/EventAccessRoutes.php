<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventAccessController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventAccessGroup) {

        $eventAccessGroup->post(
            "/create/access",
            EventAccessController::class . ":createEventAccess"
        );

        $eventAccessGroup->get(
            "/get/accesses/group/{event_id}[/{page}[/{limit}]]",
            EventAccessController::class . ":getEventAccessGroup"
        );

        $eventAccessGroup->get(
            "/get/accesses[/{event_ticket_id}[/{page}[/{limit}[/{organiser_id}[/{event_id}[/{event_access_id}[/{user_id}]]]]]]]",
            EventAccessController::class . ":getEventAccesses"
        );

        $eventAccessGroup->get(
            "/get/access/{event_access_id}",
            EventAccessController::class . ":getEventAccessByPK"
        );

        $eventAccessGroup->put(
            "/assign/access/{event_access_id}",
            EventAccessController::class . ":assignEventAccessByPK"
        );

        $eventAccessGroup->delete(
            "/delete/access/{event_access_id}",
            EventAccessController::class . ":deleteEventAccessByPK"
        );

        $eventAccessGroup->delete(
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
    function (RouteCollectorProxy $eventAccessGroup) {

        $eventAccessGroup->get(
            "/get/accesses[/{event_ticket_id}[/{page}[/{limit}[/{event_id}[/{event_access_id}[/{user_id}]]]]]]",
            EventAccessController::class . ":getOrganiserEventAccesses"
        );

        $eventAccessGroup->put(
            "/assign/access/{event_access_id}",
            EventAccessController::class . ":assignOrganiserEventAccessByPK"
        );
    }
);
