<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventTicketModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\AdminActivityLogModel;
use Liveet\Models\EventModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventTicketController extends HelperController
{

    /** Admin User */

    public function createEventTicket(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created an event ticket"]);


        return $this->createSelf(
            $request,
            $response,
            new EventTicketModel(),
            [
                "required" => [
                    "event_id", "ticket_name", "ticket_desc", "ticket_cost", "ticket_population"
                ],

                "expected" => [
                    "event_id", "ticket_name", "ticket_desc", "ticket_cost", "ticket_population", "ticket_discount",

                ],
            ]
        );
    }

    public function getEventTickets(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["event_id", "event_ticket_id", "organiser_id"];
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

            $whereHas["event"] = function ($query) use ($organiser_id) {
                return $query->where("organiser_id", $organiser_id);
            };

            unset($conditions["organiser_id"]);
        }

        return $this->getByPage($request, $response, new EventTicketModel(), null, $conditions, null, ["whereHas" => $whereHas]);
    }

    public function getEventTicketByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, new EventTicketModel(), null);
    }

    public function updateEventTicketByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an event ticket"]);


        return $this->updateByPK(
            $request,
            $response,
            new EventTicketModel(),
            [
                "required" => [
                    "ticket_name", "ticket_desc", "ticket_cost", "ticket_population"
                ],

                "expected" => [
                    "event_ticket_id", "ticket_name", "ticket_desc", "ticket_cost", "ticket_population", "ticket_discount"
                ]
            ]
        );
    }

    public function deleteEventTicketByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "deleted an event ticket"]);


        return $this->deleteByPK($request, $response, (new EventTicketModel()));
    }

    /** Organiser Staff */

    public function getOrganiserEventTickets(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["event_id", "event_ticket_id", "organiser_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas = [];

        $whereHas["event"] = function ($query) use ($organiser_id) {
            return $query->where("organiser_id", $organiser_id);
        };

        return $this->getByPage(
            $request,
            $response,
            (new EventTicketModel()),
            null,
            $conditions,
            null,
            ["whereHas" => $whereHas]
        );
    }
}
