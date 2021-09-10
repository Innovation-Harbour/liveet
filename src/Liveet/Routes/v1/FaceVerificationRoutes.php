<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\FaceVerificationLogController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $faceVerificationLogGroup) {

        $faceVerificationLogGroup->get(
            "/get/face-verification-logs[/{verification_log_id}[/{organiser_id}[/{event_id}[/{user_id}[/{page}[/{limit}]]]]]]",
            FaceVerificationLogController::class . ":getFaceVerificationLogs"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $faceVerificationLogGroup) {

        $faceVerificationLogGroup->get(
            "/get/face-verification-logs[/{verification_log_id}[/{event_id}[/{user_id}[/{page}[/{limit}]]]]]",
            FaceVerificationLogController::class . ":getOrganiserFaceVerificationLogs"
        );
    }
);
