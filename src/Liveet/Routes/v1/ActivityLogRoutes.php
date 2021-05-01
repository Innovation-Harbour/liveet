<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\ActivityLogController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->get(
            "/get/self/activity-logs[/{from}[/{to}]]",
            ActivityLogController::class . ":getSelfActivityLogs"
        );

        $eventGroup->get(
            "/get/activity-logs[/{admin_user_id}[/{from}[/{to}]]]",
            ActivityLogController::class . ":getActivityLogs"
        );

        $eventGroup->get(
            "/get/organisers/activity-logs[/{organiser_id}[/{organiser_staff_id}[/{from}[/{to}]]]]",
            ActivityLogController::class . ":getOrganiserActivityLogs"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->get(
            "/get/self/activity-logs[/{from}[/{to}]]",
            ActivityLogController::class . ":getSelfOrganiserStaffActivityLogs"
        );

        $eventGroup->get(
            "/get/activity-logs/{organiser_staff_id}[/{from}[/{to}]]",
            ActivityLogController::class . ":getOrganiserStaffActivityLogs"
        );
    }
);
