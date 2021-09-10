<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\AdminFeatureController;
use Liveet\Controllers\AdminFeatureUserController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\OrganiserStaffModel;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $adminFeatureGroup) {

        $adminFeatureGroup->post(
            "/create/feature",
            AdminFeatureController::class . ":createAdminFeature"
        );

        $adminFeatureGroup->get(
            "/get/features[/{admin_feature_id}[/{admin_user_id}[/{page}[/{limit}]]]]",
            AdminFeatureController::class . ":getAdminFeatures"
        );

        $adminFeatureGroup->get(
            "/get/feature/{admin_feature_id}",
            AdminFeatureController::class . ":getAdminFeatureByPK"
        );

        $adminFeatureGroup->put(
            "/update/feature/{admin_feature_id}",
            AdminFeatureController::class . ":updateAdminFeatureByPK"
        );

        $adminFeatureGroup->post(
            "/assign/feature",
            AdminFeatureUserController::class . ":assignAdminFeature"
        );

        $adminFeatureGroup->get(
            "/get/assigned-features[/{admin_user_id}[/{admin_feature_id}[/{page}[/{limit}]]]]",
            AdminFeatureUserController::class . ":getAssignedAdminFeatures"
        );

        $adminFeatureGroup->put(
            "/update/assigned-feature/{admin_feature_user_id}",
            AdminFeatureUserController::class . ":updateAssignedAdminFeatureByPK"
        );
    }
);
