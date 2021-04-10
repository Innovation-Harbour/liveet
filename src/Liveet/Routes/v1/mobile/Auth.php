<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\Mobile\AuthController;
use Liveet\Middlewares\AuthenticationMiddleware;


isset($mobileGroup) && $mobileGroup->group(
    "/auth",
    function (RouteCollectorProxy $authGroup) {

        $authGroup->post(
            "/register",
            AuthController::class . ":Register"
        );

        $authGroup->post(
            "/verifyotp",
            AuthController::class . ":VerifyOTP"
        );

        $authGroup->post(
            "/resendotp",
            AuthController::class . ":ResendOTP"
        );

        $authGroup->post(
            "/completeprofile",
            AuthController::class . ":CompleteProfile"
        );

        $authGroup->post(
            "/completeregistration",
            AuthController::class . ":CompleteRegistration"
        );

        $authGroup->post(
            "/login",
            AuthController::class . ":Login"
        );

        $authGroup->get(
            "/testaws",
            AuthController::class . ":testAWSAddEvent"
        );
    }
);
