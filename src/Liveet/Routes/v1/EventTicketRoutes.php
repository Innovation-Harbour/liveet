<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventTicketController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/ticket",
            EventTicketController::class . ":createEventTicket"
        );

        $eventGroup->get(
            "/get/tickets[/{event_id}[/{page}[/{limit}]]]",
            EventTicketController::class . ":getEventTickets"
        );

        $eventGroup->get(
            "/get/ticket/{event_ticket_id}",
            EventTicketController::class . ":getEventTicketByPK"
        );

        $eventGroup->put(
            "/update/ticket/{event_ticket_id}",
            EventTicketController::class . ":updateEventTicketByPK"
        );

        $eventGroup->delete(
            "/delete/ticket/{event_ticket_id}",
            EventTicketController::class . ":deleteEventTicketByPK"
        );

        $eventGroup->put(
            "/enable/ticket/{event_id}",
            EventTicketController::class . ":enableEventTicketByPk"
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
            "/get/tickets[/{event_id}[/{event_code}[/{event_type}[/{payment_type}[/{page}[/{limit}]]]]]]",
            EventTicketController::class . ":getOrganiserEventTickets"
        );
    }
);
