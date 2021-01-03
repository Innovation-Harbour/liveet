<?php

use Slim\Routing\RouteCollectorProxy;

$appGroup->group(
    '/v1',
    function (RouteCollectorProxy $group) {

        require "src/LAMATA_EPURSE/Routes/AdminRoutes.php";

        require "src/LAMATA_EPURSE/Routes/OrganizationRoutes.php";
    }
);
