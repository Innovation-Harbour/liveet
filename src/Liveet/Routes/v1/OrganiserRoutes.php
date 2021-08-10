<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\OrganiserController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\AdminUserModel;
use Liveet\Models\OrganiserModel;

/**
 * Admin priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $organiserGroup) {

        $organiserGroup->post(
            "/create/organiser",
            OrganiserController::class . ":createOrganiser"
        );

        $organiserGroup->get(
            "/get/organisers[/{page}[/{limit}]]",
            OrganiserController::class . ":getOrganisers"
        );

        $organiserGroup->get(
            "/get/organiser/{organiser_id}",
            OrganiserController::class . ":getOrganiserByPK"
        );

        $organiserGroup->put(
            "/update/organiser/{organiser_id}",
            OrganiserController::class . ":updateOrganiserByPK"
        );

        $organiserGroup->post(
            "/logout/organiser/{organiser_id}",
            OrganiserController::class . ":logoutOrganiserByPK"
        );

        $organiserGroup->put(
            "/toggle/organiser/access/{organiser_id}",
            OrganiserController::class . ":toggleOrganiserAccessStatusByPK"
        );


        /** 
         * TODO
         * 
         * Convert delete to disable
         * work on reset password 

        $organiserGroup->delete(
            "/delete/organiser/{organiser_user_id}",
            OrganiserController::class . ":deleteOrganiserByPK"
        );
         */
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new AdminUserModel())));

/**
 * 
 * Organiser Admin priviledged
 */
isset($v1Group) && $v1Group->group(
    "/organisers",
    function (RouteCollectorProxy $organiserGroup) {

        /** Admin */

        $organiserGroup->put(
            "/update/organiser-admin",
            OrganiserController::class . ":updateOrganiser"
        );

        $organiserGroup->post(
            "/logout/organiser-admin",
            OrganiserController::class . ":logoutOrganiser"
        );


        /** Admin and Staff */

        $organiserGroup->get(
            "/get/organiser-admin",
            OrganiserController::class . ":getOrganiser"
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new OrganiserModel())));

/**
 * 
 * Organiser Staff priviledged
 */
isset($v1Group) && $v1Group->group(
    "",
    function (RouteCollectorProxy $organiserGroup) {

        /** Admin */

        $organiserGroup->put(
            "/update/organiser-admin",
            OrganiserController::class . ":updateOrganiser"
        );

        $organiserGroup->post(
            "/logout/organiser-admin",
            OrganiserController::class . ":logoutOrganiser"
        );


        /** Admin and Staff */

        $organiserGroup->get(
            "/get/organiser-admin",
            OrganiserController::class . ":getOrganiser"
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new OrganiserModel())));

/**
 * No auth
 * Organiser Admin priviledged
 */
isset($v1Group) && $v1Group->group(
    "/organisers",
    function (RouteCollectorProxy $organiserGroup) {

        $organiserGroup->post(
            "/login/organiser-admin",
            OrganiserController::class . ":loginOrganiser"
        );

        $organiserGroup->get(
            "/verify/organiser-admin/email/{token}",
            OrganiserController::class . ":verifyOrganiserEmail"
        );
    }
);
