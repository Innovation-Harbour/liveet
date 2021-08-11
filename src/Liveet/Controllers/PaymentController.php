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
        $this->checkAdminPaymentPermission($request, $response);

        $expectedRouteParams = ["payment_id", "event_ticket_id", "user_id", "organiser_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["user", "eventTicket"], ["dateCreatedColumn" => "created_at"]);
    }


    /** Organiser Staff */

    public function getOrganiserPayments(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkOrganiserPaymentPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["payment_id", "event_ticket_id", "user_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["user", "eventTicket"], [
            "dateCreatedColumn" => "created_at",
            "whereHas" => [
                "eventTicket" => function ($query) use ($organiser_id) {
                    return $query->whereHas("event", function ($queryy) use ($organiser_id) {
                        return $queryy->where("organiser_id", $organiser_id);
                    });
                }
            ]
        ]);
    }
}
