<?php

use Liveet\Controllers\LocationController;
use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\TestController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\AdminModel;
use Liveet\Models\TestModel;


/**
 * Test priviledged
 */
isset($group) && $group->group(
    '/tests',
    function (RouteCollectorProxy $testGroup) {

        $testGroup->post(
            '/sync/bus/locations',

            LocationController::class . ':createManyBusLocations'
        );

        $testGroup->get(
            '/get/bus/locations/{from}/{to}',

            LocationController::class . ':getSelfBusLocations'
        );

        $testGroup->get(
            '/get/bus/locations/{from}/{to}/issuerID/{issuerID}',

            LocationController::class . ':getSelfBusLocationsByIssuerID'
        );

        $testGroup->get(
            '/get/bus/locations/{from}/{to}/busID/{busID}',

            LocationController::class . ':getSelfBusLocationsByBusID'
        );

        $testGroup->get(
            '/get/bus/locations/{from}/{to}/{issuerID}/{busID}',

            LocationController::class . ':getSelfBusLocationsByOptions'
        );

        $testGroup->get(
            '/get/issuers',

            LocationController::class . ':getIssuers'
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new TestModel())));

/**
 * No auth
 * Test priviledged
 */
isset($group) && $group->group(
    '/tests',
    function (RouteCollectorProxy $testGroup) {

        $testGroup->post(
            '/login',

            TestController::class . ':loginTest'
        );

    }
);
