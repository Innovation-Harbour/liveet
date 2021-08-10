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

        $authDetails = static::getTokenInputsFromRequest($request);

        $expectedRouteParams = ["payment_id", "event_ticket_id", "user_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["adminUser"], ["dateCreatedColumn" => "created_at"]);
    }

    public function getOrganiserPayments(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkAdminPaymentPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);

        $expectedRouteParams = ["organiser_id", "organiser_staff_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereIns = [];
        if (isset($conditions["organiser_id"])) {
            $organiser_ids = $this->getOrganiserStaffIds($conditions["organiser_id"]);
            unset($conditions["organiser_id"]);
            $whereIn[] = ["organiser_staff_id" => $organiser_ids];
        }

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["organiserStaff"], ["dateCreatedColumn" => "created_at", "whereIn" => $whereIns]);
    }


    /** Organiser Staff */

    public function getSelfOrganiserStaffPayments(Request $request, ResponseInterface $response): ResponseInterface
    {
        // $this->checkOrganiserPaymentPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_staff_id = $authDetails["organiser_staff_id"];
        $conditions = ["organiser_staff_id" => $organiser_staff_id];

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["organiserStaff"]);
    }

    public function getOrganiserStaffPayments(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkOrganiserPaymentPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $organiser_staff_ids = $this->getOrganiserStaffIds($organiser_id);

        $expectedRouteParams = ["organiser_staff_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        if (isset($conditions["organiser_staff_id"])) {
            $this->organiserStaffBelongsToOrganiser($request, $response, $conditions["organiser_staff_id"]);
        }

        return $this->getByDate($request, $response, new PaymentModel(), null, $conditions, ["organiserStaff"], ["whereIn" => [["organiser_staff_id" => $organiser_staff_ids]], "dateCreatedColumn" => "created_at"]);
    }
}
