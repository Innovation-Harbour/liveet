<?php

namespace Liveet\Controllers;

use Liveet\Models\TurnstileModel;
use Liveet\Models\ActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class TurnstileController extends HelperController
{

    /** Admin Turnstile */

    public function getTurnstiles(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkAdminTurnstilePermission($request, $response);

        $expectedRouteParams = ["turnstile_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new TurnstileModel(), null, $conditions, ["events"]);
    }
}
