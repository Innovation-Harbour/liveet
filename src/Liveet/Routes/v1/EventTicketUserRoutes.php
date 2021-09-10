<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventTicketUserController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventTicketUserGroup) {

        $eventTicketUserGroup->post(
            "/create/ticket-user",
            EventTicketUserController::class . ":createEventTicketUser"
        );

        $eventTicketUserGroup->get(
            "/get/ticket-users[/{event_id}[/{event_ticket_id}[/{from}[/{to}[/{page}[/{limit}]]]]]]",
            EventTicketUserController::class . ":getEventTicketUsers"
        );

        $eventTicketUserGroup->get(
            "/get/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":getEventTicketUserByPK"
        );

        $eventTicketUserGroup->put(
            "/transfer/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":transferEventTicketUserByPK"
        );

        $eventTicketUserGroup->delete(
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
    function (RouteCollectorProxy $eventTicketUserGroup) {

        $eventTicketUserGroup->post(
            "/create/ticket-user",
            EventTicketUserController::class . ":createOrganiserEventTicketUser"
        );

        $eventTicketUserGroup->get(
            "/get/ticket-users[/{event_id}[/{event_ticket_id}[/{from}[/{to}[/{page}[/{limit}]]]]]]",
            EventTicketUserController::class . ":getOrganiserEventTicketUsers"
        );

        $eventTicketUserGroup->get(
            "/get/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":getOrganiserEventTicketUserByPK"
        );

        $eventTicketUserGroup->put(
            "/transfer/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":transferOrganiserEventTicketUserByPK"
        );

        $eventTicketUserGroup->delete(
            "/recall/ticket-user/{event_ticket_user_id}",
            EventTicketUserController::class . ":recallOrganiserEventTicketUser"
        );
    }
);
