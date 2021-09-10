<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventTicketController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventTicketGroup) {

        $eventTicketGroup->post(
            "/create/ticket",
            EventTicketController::class . ":createEventTicket"
        );

        $eventTicketGroup->get(
            "/get/tickets[/{event_id}[/{page}[/{limit}[/{event_ticket_id}[/{organiser_id}]]]]]",
            EventTicketController::class . ":getEventTickets"
        );

        $eventTicketGroup->get(
            "/get/ticket/{event_ticket_id}",
            EventTicketController::class . ":getEventTicketByPK"
        );

        $eventTicketGroup->put(
            "/update/ticket/{event_ticket_id}",
            EventTicketController::class . ":updateEventTicketByPK"
        );

        $eventTicketGroup->delete(
            "/delete/ticket/{event_ticket_id}",
            EventTicketController::class . ":deleteEventTicketByPK"
        );

        $eventTicketGroup->put(
            "/enable/ticket/{event_id}",
            EventTicketController::class . ":enableEventTicketByPk"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $eventTicketGroup) {

        $eventTicketGroup->get(
            "/get/tickets[/{event_id}[/{page}[/{limit}[/{event_ticket_id}]]]]",
            EventTicketController::class . ":getOrganiserEventTickets"
        );
    }
);
