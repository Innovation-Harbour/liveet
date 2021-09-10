<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\ReportController;


/**
 * Admin Report Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $reportGroup) {

        $reportGroup->get(
            "/get/organiser/summary/{organiser_id}[/{event_id}]",
            ReportController::class . ":getOrganiserSummary"
        );

        $reportGroup->get(
            "/get/organiser/timely-summary/{organiser_id}/{from}/{to}/{interval}[/{event_id}]",
            ReportController::class . ":getOrganiserTimelySummary"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $reportGroup) {

        $reportGroup->get(
            "/get/organiser/timely-summary/{from}/{to}/{interval}[/{event_id}]",
            ReportController::class . ":getOrganiserSelfTimelySummary"
        );
    }
);
