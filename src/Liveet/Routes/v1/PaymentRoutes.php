<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\PaymentController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $paymentGroup) {

        $paymentGroup->get(
            "/get/payments[/{payment_id}[/{event_ticket_id}[/{user_id}[/{organiser_id}[/{from}[/{to}]]]]]]",
            PaymentController::class . ":getPayments"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $paymentGroup) {

        $paymentGroup->get(
            "/get/payments[/{payment_id}[/{event_ticket_id}[/{user_id}[/{from}[/{to}]]]]]",
            PaymentController::class . ":getOrganiserPayments"
        );
    }
);
