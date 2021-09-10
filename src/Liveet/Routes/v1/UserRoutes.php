<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\UserController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $userGroup) {

        $userGroup->get(
            "/get/users[/{user_id}[/{user_phone}[/{fcm_token}[/{page}[/{limit}]]]]]",
            UserController::class . ":getUsers"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $userGroup) {
    }
);
