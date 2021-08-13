<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\OrganiserStaffController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\OrganiserStaffModel;


/**
 * No auth
 * Organiser Admin and Staff Priviledged
 */
isset($v1Group) && $v1Group->group(
    "/organisers",
    function (RouteCollectorProxy $organiserStaffGroup) {

        $organiserStaffGroup->post(
            "/login/organiser-staff",
            OrganiserStaffController::class . ":loginOrganiserAdminOrStaff"
        );

        $organiserStaffGroup->get(
            "/verify/organiser-staff/email/{token}",
            OrganiserStaffController::class . ":verifyOrganiserStaffEmail"
        );
    }
);

/**
 * Organiser Admin and Staff Priviledged
 */
isset($v1Group) && $v1Group->group(
    "/organisers",
    function (RouteCollectorProxy $organiserStaffGroup) {

        /**
         * Activity Log Routes
         */
        require "src/Liveet/Routes/v1/ActivityLogRoutes.php";

        /**
         * Organiser Routes
         */
        require "src/Liveet/Routes/v1/OrganiserRoutes.php";

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
         * Helper Routes
         */
        require "src/Liveet/Routes/v1/HelperRoutes.php";


        /** Organiser Admin and Staff */

        $organiserStaffGroup->get(
            "/get/organiser-staff/dashboard",
            OrganiserStaffController::class . ":getOrganiserAdminOrStaffDashboard"
        );

        $organiserStaffGroup->get(
            "/get/organiser-staff",
            OrganiserStaffController::class . ":getOrganiserAdminOrStaff"
        );

        $organiserStaffGroup->put(
            "/update/organiser-staff/password",
            OrganiserStaffController::class . ":updateOrganiserAdminOrStaffPassword"
        );

        $organiserStaffGroup->put(
            "/update/organiser-staff",
            OrganiserStaffController::class . ":updateOrganiserAdminOrStaff"
        );

        $organiserStaffGroup->post(
            "/logout/organiser-staff",
            OrganiserStaffController::class . ":logoutOrganiserAdminOrStaff"
        );

        $organiserStaffGroup->put(
            "/toggle/organiser-staff/access/{organiser_staff_id}",
            OrganiserStaffController::class . ":toggleOrganiserSelfStaffAccessStatusByPK"
        );

        /**
         * TODO 
         * convert to disable
         * 
        $organiserStaffGroup->delete(
            "/delete/organiser",
            OrganiserStaffController::class . ":deleteOrganiserStaff"
        );
         */

        /** Organiser Admin */

        $organiserStaffGroup->post(
            "/create/organiser-staff",
            OrganiserStaffController::class . ":createOrganiserSelfStaff"
        );

        $organiserStaffGroup->get(
            "/get/organiser-staffs[/{page}[/{limit}]]",
            OrganiserStaffController::class . ":getOrganiserSelfStaffs"
        );

        $organiserStaffGroup->get(
            "/get/organiser-staff/{organiser_staff_id}",
            OrganiserStaffController::class . ":getOrganiserSelfStaffByPK"
        );

        $organiserStaffGroup->put(
            "/update/organiser-staff/{organiser_staff_id}",
            OrganiserStaffController::class . ":updateOrganiserSelfStaffByPK"
        );

        $organiserStaffGroup->post(
            "/logout/organiser-staff/{organiser_staff_id}",
            OrganiserStaffController::class . ":logoutOrganiserSelfStaffByPK"
        );

        /** 
         * TODO
         * 
         * Convert delete to disable
         * work on reset password 
        
        $organiserStaffGroup->put(
            "/reset/organiser/password",
            OrganiserStaffController::class . ":resetOrganiserStaffPassword"
        );

        $organiserStaffGroup->delete(
            "/delete/organiser/{organiser_user_id}",
            OrganiserStaffController::class . ":deleteOrganiserStaffByPK"
        );
         */
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new OrganiserStaffModel())));


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $organiserStaffGroup) {

        $organiserStaffGroup->get(
            "/get/organiser-staffs[/{page}[/{limit}]]",
            OrganiserStaffController::class . ":getOrganiserStaffs"
        );

        $organiserStaffGroup->get(
            "/get/organiser-staff/{organiser_staff_id}",
            OrganiserStaffController::class . ":getOrganiserStaffByPK"
        );

        $organiserStaffGroup->post(
            "/logout/organiser-staff/{organiser_staff_id}",
            OrganiserStaffController::class . ":logoutOrganiserStaffByPK"
        );

        $organiserStaffGroup->put(
            "/toggle/organiser-staff/access/{organiser_staff_id}",
            OrganiserStaffController::class . ":toggleOrganiserStaffAccessStatusByPK"
        );
    }
);
