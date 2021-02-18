<?php

use Slim\Routing\RouteCollectorProxy;

$appGroup->group(
    '/v1',
    function (RouteCollectorProxy $group) {

        // require "src/Liveet/Routes/AdminRoutes.php";

        // require "src/Liveet/Routes/OrganizationRoutes.php";

        require "src/Liveet/Routes/TestRoutes.php";
    }
);
