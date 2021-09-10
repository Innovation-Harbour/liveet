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
         * Activity Log Routes
         */
        require "src/Liveet/Routes/v1/ActivityLogRoutes.php";

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

        /**
         * Event Routes
         */
        require "src/Liveet/Routes/v1/EventRoutes.php";

        /**
         * Event Ticket Routes
         */
        require "src/Liveet/Routes/v1/EventTicketRoutes.php";

        /**
         * Event Invitation Routes
         */
        require "src/Liveet/Routes/v1/EventInvitationRoutes.php";

        /**
         * Event Access Routes
         */
        require "src/Liveet/Routes/v1/EventAccessRoutes.php";

        /**
         * Event Ticket-User Routes
         */
        require "src/Liveet/Routes/v1/EventTicketUserRoutes.php";

        /**
         * Event Timeline Routes
         */
        require "src/Liveet/Routes/v1/EventTimelineRoutes.php";

        /**
         * Timeline Media Routes
         */
        require "src/Liveet/Routes/v1/TimelineMediaRoutes.php";

        /**
         * Payment Routes
         */
        require "src/Liveet/Routes/v1/PaymentRoutes.php";

        /**
         * User Routes
         */
        require "src/Liveet/Routes/v1/UserRoutes.php";

        /**
         * Face Verification Routes
         */
        require "src/Liveet/Routes/v1/FaceVerificationRoutes.php";

        /**
         * Turnstile Routes
         */
        require "src/Liveet/Routes/v1/TurnstileRoutes.php";

        /**
         * Report Routes
         */
        require "src/Liveet/Routes/v1/ReportRoutes.php";

        /**
         * Helper Routes
         */
        require "src/Liveet/Routes/v1/HelperRoutes.php";


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

        $adminGroup->put(
            "/disable/admin/access-status",
            AdminUserController::class . ":disableAdminAccessStatus"
        );

        $adminGroup->post(
            "/logout/admin",
            AdminUserController::class . ":logoutAdminUser"
        );


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

        $adminGroup->put(
            "/reset/admin/password/{admin_user_id}",
            AdminUserController::class . ":resetAdminUserPasswordByPK"
        );

        $adminGroup->post(
            "/logout/admin/{admin_user_id}",
            AdminUserController::class . ":logoutAdminUserByPK"
        );

        $adminGroup->put(
            "/toggle/admin/access/{admin_user_id}",
            AdminUserController::class . ":toggleAdminUserAccessStatusByPK"
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
