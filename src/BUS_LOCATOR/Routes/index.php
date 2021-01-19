<?php

use Slim\Routing\RouteCollectorProxy;

$appGroup->group(
    '/v1',
    function (RouteCollectorProxy $group) {

        require "src/BUS_LOCATOR/Routes/AdminRoutes.php";

        require "src/BUS_LOCATOR/Routes/OrganizationRoutes.php";
    }
);
