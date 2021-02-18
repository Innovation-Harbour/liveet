<?php

use Liveet\Controllers\LocationController;
use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\OrganizationController;
use Liveet\Middlewares\AuthenticationMiddleware;
use Liveet\Models\AdminModel;
use Liveet\Models\OrganizationModel;


/**
 * Organization priviledged
 */
isset($group) && $group->group(
    '/organizations',
    function (RouteCollectorProxy $organizationGroup) {

        $organizationGroup->post(
            '/sync/bus/locations',

            LocationController::class . ':createManyBusLocations'
        );

        $organizationGroup->get(
            '/get/bus/locations/{from}/{to}',

            LocationController::class . ':getSelfBusLocations'
        );

        $organizationGroup->get(
            '/get/bus/locations/{from}/{to}/issuerID/{issuerID}',

            LocationController::class . ':getSelfBusLocationsByIssuerID'
        );

        $organizationGroup->get(
            '/get/bus/locations/{from}/{to}/busID/{busID}',

            LocationController::class . ':getSelfBusLocationsByBusID'
        );

        $organizationGroup->get(
            '/get/bus/locations/{from}/{to}/{issuerID}/{busID}',

            LocationController::class . ':getSelfBusLocationsByOptions'
        );

        $organizationGroup->get(
            '/get/issuers',

            LocationController::class . ':getIssuers'
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new OrganizationModel())));

/**
 * No auth
 * Organization priviledged
 */
isset($group) && $group->group(
    '/organizations',
    function (RouteCollectorProxy $organizationGroup) {

        $organizationGroup->post(
            '/login/organization',

            OrganizationController::class . ':loginOrganization'
        );

        $organizationGroup->post(
            '/authenticate/organization',

            OrganizationController::class . ':authenticateOrganization'
        );
    }
);

/**
 * Admin priviledged
 */
isset($adminGroup) && $adminGroup->group(
    '',
    function (RouteCollectorProxy $organizationGroup) {

        $organizationGroup->post(
            '/create/organization',

            OrganizationController::class . ':createOrganization'
        );

        $organizationGroup->get(
            '/get/organizations[/{page}[/{limit}]]',

            OrganizationController::class . ':getOrganizations'
        );

        $organizationGroup->get(
            '/get/organization/{id}',

            OrganizationController::class . ':getOrganizationById'
        );

        $organizationGroup->get(
            '/get/organization/{id}/{property}',

            OrganizationController::class . ':getOrganizationByIdProperty'
        );

        $organizationGroup->put(
            '/update/organization/{id}/generate/public_key',

            OrganizationController::class . ':updateOrganizationByIdPublicKey'
        );
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new AdminModel())));
