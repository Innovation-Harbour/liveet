<?php

use Slim\Routing\RouteCollectorProxy;

isset($appGroup) && $appGroup->group(
    "",
    function (RouteCollectorProxy $vGroup) {

        require "src/Liveet/Routes/v1/index.php";
    }
);
