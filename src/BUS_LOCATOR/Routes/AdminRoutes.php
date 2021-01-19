<?php

use Slim\Routing\RouteCollectorProxy;

use BUS_LOCATOR\Controllers\AdminController;
use BUS_LOCATOR\Middlewares\AuthenticationMiddleware;
use BUS_LOCATOR\Models\AdminModel;

/**
 * Admin priviledged
 */
isset($group) && $group->group(
    '/admins',
    function (RouteCollectorProxy $adminGroup) {

        /**
         * Organization Routes
         */
        require 'src/BUS_LOCATOR/Routes/OrganizationRoutes.php';


        $adminGroup->post(
            '/create/admin',
            AdminController::class . ':createAdmin'
        );


        $adminGroup->get(
            '/get/admin/dashboard',
            AdminController::class . ':getAdminDashboard'
        );

        $adminGroup->get(
            '/get/admins/{page}[/{limit}]',
            AdminController::class . ':getAllAdmins'
        );

        $adminGroup->get(
            '/get/admin',
            AdminController::class . ':getAdmin'
        );

        $adminGroup->get(
            '/get/admin/{id}',
            AdminController::class . ':getAdminById'
        );

        $adminGroup->get(
            '/verify/email/{token}',
            AdminController::class . ':verifyAdminEmail'
        );

        $adminGroup->put(
            '/update/admin/password',
            AdminController::class . ':updateAdminPassword'
        );

        $adminGroup->put(
            '/update/admin',
            AdminController::class . ':updateAdmin'
        );

        $adminGroup->put(
            '/update/admin/{id}',
            AdminController::class . ':updateAdminById'
        );

        $adminGroup->put(
            '/reset/admin/password',
            AdminController::class . ':resetAdminPassword'
        );

        $adminGroup->delete(
            '/delete/admin',
            AdminController::class . ':deleteAdmin'
        );

        $adminGroup->delete(
            '/delete/admin/{id}',
            AdminController::class . ':deleteAdminById'
        );

        $adminGroup->post(
            '/logout/admin/{id}',
            AdminController::class . ':logoutAdminById'
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new AdminModel())));

/**
 * No auth
 * Admin priviledged
 */
isset($group) && $group->group(
    '/admins',
    function (RouteCollectorProxy $adminGroup) {

        // $adminGroup->post(
        //     '/create/admin',
        //     AdminController::class . ':createAdmin'
        // );

        $adminGroup->post(
            '/login/admin',
            AdminController::class . ':loginAdmin'
        );

        $adminGroup->post(
            '/logout/admin',
            AdminController::class . ':logoutAdmin'
        );
    }
);
