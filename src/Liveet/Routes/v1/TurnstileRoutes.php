<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\TurnstileController;


/**
 * Admin Turnstile Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $turnstileGroup) {

        $turnstileGroup->get(
            "/get/turnstiles[/{turnstile_id}[/{organiser_id}[/{event_id}[/{page}[/{limit}]]]]]",
            TurnstileController::class . ":getTurnstiles"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $turnstileGroup) {
    }
);
