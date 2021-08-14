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
        $permissonResponse = $this->checkAdminTurnstilePermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["turnstile_id", "event_id", "organiser_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas = [];
        if (isset($conditions["event_id"])) {
            $event_id = $conditions["event_id"];

            $whereHas["eventTickets"] = function ($query) use ($event_id) {
                return $query->where("event_id", $event_id);
            };

            unset($conditions["event_id"]);
        }

        if (isset($conditions["organiser_id"])) {
            $organiser_id = $conditions["organiser_id"];

            $whereHas["eventTickets"] = function ($query) use ($organiser_id) {
                return $query->whereHas("event", function ($query) use ($organiser_id) {
                    return $query->where("organiser_id", $organiser_id);
                });
            };

            unset($conditions["organiser_id"]);
        }

        return $this->getByPage($request, $response, new TurnstileModel(), null, $conditions, ["eventTickets"], ["whereHas" => $whereHas]);
    }
}
