<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventTicketModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\EventModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventTicketController extends BaseController
{

    /** Admin User */

    public function createEventTicket(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->createSelf(
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
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        $expectedRouteParams = ["event_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return (new BaseController)->getByPage($request, $response, new EventTicketModel(), null, $conditions);
    }

    public function getEventTicketByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPK($request, $response, new EventTicketModel(), null);
    }

    public function updateEventTicketByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }

        return (new BaseController)->updateByPK(
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
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }

        return (new BaseController)->deleteByPK($request, $response, (new EventTicketModel()));
    }

    /** Organiser Staff */

    public function getOrganiserEventTickets(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_EVENT, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];
        $event_ids = (new EventModel())->select("event_id")->where("organiser_id", $organiser_id)->without("eventControl", "eventTickets")->get();
        $whereInEventIds = [];
        foreach ($event_ids as $event_id_value) {
            $whereInEventIds[] = $event_id_value["event_id"];
        }

        $routeParams = $this->getRouteParams($request);
        if (isset($routeParams["event_id"]) && $routeParams["event_id"] != "-") {
            $conditions["event_id"] = $routeParams["event_id"];

            if (in_array($routeParams["event_id"], $whereInEventIds)) {
                return (new BaseController)->getByPage(
                    $request,
                    $response,
                    (new EventTicketModel()),
                    null,
                    $conditions
                );
            }

            $payload = array("errorMessage" => "No tickets for this event yet", "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }



        return (new BaseController)->getByPage(
            $request,
            $response,
            (new EventTicketModel()),
            null,
            null,
            null,
            [
                "whereIn" => [
                    ["event_id" => $whereInEventIds],
                ]
            ]
        );
    }
}
