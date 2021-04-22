<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventTicketUserController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/ticket-user",
            EventTicketUserController::class . ":createEventTicketUser"
        );

        $eventGroup->get(
            "/get/ticket-users[/{event_id}[/{event_ticket_id}[/{from}[/{to}[/{page}[/{limit}]]]]]]",
            EventTicketUserController::class . ":getEventTicketUsers"
        );

        $eventGroup->get(
            "/get/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":getEventTicketUserByPK"
        );

        $eventGroup->put(
            "/transfer/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":transferEventTicketUserByPK"
        );

        $eventGroup->delete(
            "/recall/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":recallEventTicketUser"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/ticket-user",
            EventTicketUserController::class . ":createOrganiserEventTicketUser"
        );

        $eventGroup->get(
            "/get/ticket-users[/{event_id}[/{event_ticket_id}[/{from}[/{to}[/{page}[/{limit}]]]]]]",
            EventTicketUserController::class . ":getOrganiserEventTicketUsers"
        );

        $eventGroup->get(
            "/get/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":getOrganiserEventTicketUserByPK"
        );

        $eventGroup->put(
            "/transfer/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":transferOrganiserEventTicketUserByPK"
        );

        $eventGroup->delete(
            "/recall/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":recallOrganiserEventTicketUser"
        );
    }
);
