<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\ReportController;


/**
 * Admin Report Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $report) {

        $report->get(
            "/get/organiser/summary/{organiser_id}",
            ReportController::class . ":getOrganiserSummary"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $report) {
    }
);
