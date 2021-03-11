<?php

use Slim\Routing\RouteCollectorProxy;

$vGroup->group(
    '/v1',
    function (RouteCollectorProxy $v1Group) {

        require "src/Liveet/Routes/v1/AdminRoutes.php";

        // require "src/Liveet/Routes/v1/OrganizationRoutes.php";

        // require "src/Liveet/Routes/v1/TestRoutes.php";
    }
);
