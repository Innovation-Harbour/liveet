<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventAccessModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventAccessController extends BaseController
{

    /** Admin User */

    public function createEventAccess(Request $request, ResponseInterface $response): ResponseInterface
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
            new EventAccessModel(),
            [
                "required" => [
                    "event_ticket_id", "event_access_population"
                ],

                "expected" => [
                    "event_ticket_id", "event_access_population"

                ],
            ]
        );
    }

    public function getEventAccesses(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        $expectedRouteParams = ["event_ticket_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return (new BaseController)->getByPage($request, $response, new EventAccessModel(), null, $conditions, ["user"]);
    }

    public function getEventAccessByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPK($request, $response, new EventAccessModel(), null);
    }

    public function applyEventAccessByPK(Request $request, ResponseInterface $response): ResponseInterface
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
            new EventAccessModel(),
            [
                "required" => [
                    "user_phone"
                ],

                "expected" => [
                    "user_phone"
                ]
            ]
        );
    }

    public function deleteEventAccessByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }

        return (new BaseController)->deleteByPK($request, $response, (new EventAccessModel()));
    }

    public function deleteEventAccessByPKs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }

        return (new BaseController)->deleteManyByPK($request, $response, (new EventAccessModel()));
    }

    /** Organiser Staff */

    public function getOrganiserEventAccesses(Request $request, ResponseInterface $response): ResponseInterface
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

        $event_id_s = (new EventModel())->select("event_id")->where("organiser_id", $organiser_id)->without("eventControl", "eventTickets")->get();

        $event_ids = [];
        foreach ($event_id_s as $event_id_value) {
            $event_ids[] = $event_id_value["event_id"];
        }

        $event_ticket_id_s = (new EventTicketModel())->select("event_ticket_id")->whereIn("event_id", $event_ids)->get();

        $whereInEventTicketIds = [];
        foreach ($event_ticket_id_s as $event_ticket_id_value) {
            $whereInEventTicketIds[] = $event_ticket_id_value["event_ticket_id"];
        }

        $routeParams = $this->getRouteParams($request);
        if (isset($routeParams["event_ticket_id"]) && $routeParams["event_ticket_id"] != "-") {
            $conditions["event_ticket_id"] = $routeParams["event_ticket_id"];
            if (in_array($routeParams["event_ticket_id"], $whereInEventTicketIds)) {

                var_dump($model->toArray());
                return (new BaseController)->getByPage(
                    $request,
                    $response,
                    (new EventAccessModel()),
                    null,
                    $conditions
                );
            }

            $payload = array("errorMessage" => "No access codes for this ticket yet", "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }



        return (new BaseController)->getByPage(
            $request,
            $response,
            (new EventAccessModel()),
            null,
            null,
            null,
            [
                "whereIn" => [
                    ["event_ticket_id" => $whereInEventTicketIds],
                ]
            ]
        );
    }
}
