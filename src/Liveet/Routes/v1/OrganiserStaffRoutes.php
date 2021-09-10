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


        /** Organiser Admin */

        $organiserStaffGroup->post(
            "/create/organiser-staff",
            OrganiserStaffController::class . ":createOrganiserSelfStaff"
        );

        $organiserStaffGroup->get(
            "/get/organiser-staffs[/{page}[/{limit}[/{organiser_staff_id}]]]",
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
            "/get/organiser-staffs[/{page}[/{limit}[/{organiser_staff_id}[/{organiser_id}]]]]",
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
