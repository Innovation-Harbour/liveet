<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\AdminUserController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\AdminUserModel;

/**
 * Admin priviledged
 */
isset($v1Group) && $v1Group->group(
    '/admins',
    function (RouteCollectorProxy $adminGroup) {

        /**
         * Organization Routes
         */
        // require 'src/Liveet/Routes/v1/OrganizationRoutes.php';


        $adminGroup->post(
            '/create/admin',
            AdminUserController::class . ':createAdminUser'
        );

        $adminGroup->get(
            '/get/admin/dashboard',
            AdminUserController::class . ':getAdminUserDashboard'
        );

        $adminGroup->get(
            '/get/admins[/{page}[/{limit}]]',
            AdminUserController::class . ':getAllAdminUsers'
        );

        $adminGroup->get(
            '/get/admin',
            AdminUserController::class . ':getAdmin'
        );

        $adminGroup->get(
            '/get/admin/{id}',
            AdminUserController::class . ':getAdminById'
        );

        $adminGroup->get(
            '/verify/email/{token}',
            AdminUserController::class . ':verifyAdminEmail'
        );

        $adminGroup->put(
            '/update/admin/password',
            AdminUserController::class . ':updateAdminPassword'
        );

        $adminGroup->put(
            '/update/admin',
            AdminUserController::class . ':updateAdmin'
        );

        $adminGroup->put(
            '/update/admin/{id}',
            AdminUserController::class . ':updateAdminById'
        );

        $adminGroup->put(
            '/reset/admin/password',
            AdminUserController::class . ':resetAdminPassword'
        );

        $adminGroup->delete(
            '/delete/admin',
            AdminUserController::class . ':deleteAdmin'
        );

        $adminGroup->delete(
            '/delete/admin/{id}',
            AdminUserController::class . ':deleteAdminById'
        );

        $adminGroup->post(
            '/logout/admin/{id}',
            AdminUserController::class . ':logoutAdminById'
        );

        $adminGroup->post(
            '/generate/hash',
            AdminUserController::class . ':generateHash'
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new AdminUserModel())));

/**
 * No auth
 * Admin priviledged
 */
isset($v1Group) && $v1Group->group(
    '/admins',
    function (RouteCollectorProxy $adminGroup) {

        $adminGroup->post(
            '/login/admin',
            AdminUserController::class . ':loginAdminUser'
        );

        $adminGroup->post(
            '/logout/admin',
            AdminUserController::class . ':logoutAdmin'
        );
    }
);
