<?php

use Slim\Routing\RouteCollectorProxy;

isset($v1Group) && $v1Group->group(
    "/m",
    function (RouteCollectorProxy $mobileGroup) {

        require "src/Liveet/Routes/v1/mobile/Auth.php";
    }
);
