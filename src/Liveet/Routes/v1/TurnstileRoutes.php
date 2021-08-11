<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\TurnstileController;


/**
 * Admin Turnstile Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $turnstile) {

        $turnstile->get(
            "/get/turnstiles[/{turnstile_id}[/{page}[/{limit}]]]",
            TurnstileController::class . ":getTurnstiles"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $turnstile) {
    }
);
