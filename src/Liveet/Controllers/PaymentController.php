<?php

namespace Liveet\Controllers;

use Liveet\Models\PaymentModel;
use Liveet\Models\ActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class PaymentController extends HelperController
{

    /** Admin User */

    public function getPayments(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminPaymentPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["payment_id", "event_ticket_id", "user_phone", "organiser_id", "event_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas = [];
        if (isset($conditions["organiser_id"])) {
            $organiser_id = $conditions["organiser_id"];

            $whereHas["eventTicket"] = function ($query) use ($organiser_id) {
                return $query->whereHas("event", function ($query) use ($organiser_id) {
                    return $query->where("organiser_id", $organiser_id);
                });
            };

            unset($conditions["organiser_id"]);
        }

        if (isset($conditions["event_id"])) {
            $event_id = $conditions["event_id"];

            $whereHas["eventTicket"] = function ($query) use ($event_id) {
                return $query->where("event_id", $event_id);
            };

            unset($conditions["event_id"]);
        }

        if (isset($conditions["user_phone"])) {
            $user_phone = $conditions["user_phone"];

            $whereHas["user"] = function ($query) use ($user_phone) {
                return $query->where("user_phone", "LIKE", "%$user_phone%");
            };

            unset($conditions["user_phone"]);
        }

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["user", "eventTicket"], ["dateCreatedColumn" => "created_at", "whereHas" => $whereHas]);
    }


    /** Organiser Staff */

    public function getOrganiserPayments(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserPaymentPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["payment_id", "event_ticket_id", "user_phone", "event_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas["eventTicket"] = function ($query) use ($organiser_id) {
            return $query->whereHas("event", function ($query) use ($organiser_id) {
                return $query->where("organiser_id", $organiser_id);
            });
        };

        if (isset($conditions["event_id"])) {
            $event_id = $conditions["event_id"];

            $whereHas["eventTicket"] = function ($query) use ($event_id) {
                return $query->where("event_id", $event_id);
            };

            unset($conditions["event_id"]);
        }

        if (isset($conditions["user_phone"])) {
            $user_phone = $conditions["user_phone"];

            $whereHas["user"] = function ($query) use ($user_phone) {
                return $query->where("user_phone", "LIKE", "%$user_phone%");
            };

            unset($conditions["user_phone"]);
        }

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["user", "eventTicket"], [
            "dateCreatedColumn" => "created_at",
            "whereHas" => $whereHas
        ]);
    }
}
