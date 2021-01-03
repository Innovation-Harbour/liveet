<?php

use LAMATA_EPURSE\Controllers\TransactionController;
use Slim\Routing\RouteCollectorProxy;

use LAMATA_EPURSE\Controllers\OrganizationController;
use LAMATA_EPURSE\Middlewares\AuthenticationMiddleware;
use LAMATA_EPURSE\Models\AdminModel;
use LAMATA_EPURSE\Models\OrganizationModel;


/**
 * Organization priviledged
 */
isset($group) && $group->group(
    '/organizations',
    function (RouteCollectorProxy $organizationGroup) {

        $organizationGroup->post(
            '/sync/bus/transactions',

            TransactionController::class . ':createManyBusTransaction'
        );

        $organizationGroup->get(
            '/get/bus/transactions/{from}/{to}',

            TransactionController::class . ':getSelfBusTransaction'
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
    }
)
    ->addMiddleware(new AuthenticationMiddleware((new AdminModel())));
