<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\AdminUserController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\AdminUserModel;

/**
 * Admin User priviledged
 */
isset($v1Group) && $v1Group->group(
    "/admins",
    function (RouteCollectorProxy $adminGroup) {

        /**
         * Organiser Routes
         */
        require "src/Liveet/Routes/v1/OrganiserRoutes.php";

        /**
         * Organiser Staff Routes
         */
        require "src/Liveet/Routes/v1/OrganiserStaffRoutes.php";

        /**
         * Admin Feature Routes
         */
        require "src/Liveet/Routes/v1/AdminFeatureRoutes.php";


        /** */

        $adminGroup->get(
            "/get/admin/dashboard",
            AdminUserController::class . ":getAdminUserDashboard"
        );

        $adminGroup->get(
            "/get/admin",
            AdminUserController::class . ":getAdminUser"
        );

        $adminGroup->put(
            "/update/admin/password",
            AdminUserController::class . ":updateAdminUserPassword"
        );

        $adminGroup->put(
            "/update/admin",
            AdminUserController::class . ":updateAdminUser"
        );

        $adminGroup->post(
            "/logout/admin",
            AdminUserController::class . ":logoutAdminUser"
        );

        /**
         * TODO 
         * convert to disable
         * 
        $adminGroup->delete(
            "/delete/admin",
            AdminUserController::class . ":deleteAdminUser"
        );
         */


        /**ADMIN Priviledge */

        $adminGroup->post(
            "/create/admin",
            AdminUserController::class . ":createAdminUser"
        );

        $adminGroup->get(
            "/get/admins[/{page}[/{limit}]]",
            AdminUserController::class . ":getAdminUsers"
        );

        $adminGroup->get(
            "/get/admin/{admin_user_id}",
            AdminUserController::class . ":getAdminUserByPK"
        );

        $adminGroup->put(
            "/update/admin/{admin_user_id}",
            AdminUserController::class . ":updateAdminUserByPK"
        );

        $adminGroup->post(
            "/logout/admin/{admin_user_id}",
            AdminUserController::class . ":logoutAdminUserByPK"
        );

        /** 
         * TODO
         * 
         * Convert delete to disable
         * work on reset password 
        
        $adminGroup->put(
            "/reset/admin/password",
            AdminUserController::class . ":resetAdminUserPassword"
        );

        $adminGroup->delete(
            "/delete/admin/{admin_user_id}",
            AdminUserController::class . ":deleteAdminUserByPK"
        );
         */

        $adminGroup->post(
            "/generate/hash",
            AdminUserController::class . ":generateHash"
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new AdminUserModel())));

/**
 * No auth
 * Admin User priviledged
 */
isset($v1Group) && $v1Group->group(
    "/admins",
    function (RouteCollectorProxy $adminGroup) {

        $adminGroup->post(
            "/login/admin",
            AdminUserController::class . ":loginAdminUser"
        );

        $adminGroup->get(
            "/verify/admin/email/{token}",
            AdminUserController::class . ":verifyAdminUserEmail"
        );
    }
);
