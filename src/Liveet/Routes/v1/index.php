<?php

use Slim\Routing\RouteCollectorProxy;

$vGroup->group(
    "/v1",
    function (RouteCollectorProxy $v1Group) {

        require "src/Liveet/Routes/v1/AdminUserRoutes.php";

        require "src/Liveet/Routes/v1/OrganiserRoutes.php";

        require "src/Liveet/Routes/v1/OrganiserStaffRoutes.php";

        require "src/Liveet/Routes/v1/TestRoutes.php";
    }
);
