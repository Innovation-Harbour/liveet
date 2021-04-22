<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventAccessModel;
use Liveet\Controllers\BaseController;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventAccessController extends HelperController
{

    /** Admin User */

    public function createEventAccess(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->createSelf(
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

    public function getEventAccessGroup(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkAdminEventPermission($request, $response);
        ["event_id" => $event_id] = $this->getRouteParams($request, ["event_id"]);

        return $this->getSelfDashboard($request, $response, new EventAccessModel(), [], ["event_id" => $event_id, "hasKey" => false]);
    }

    public function getEventAccesses(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        $expectedRouteParams = ["event_ticket_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new EventAccessModel(), null, $conditions, ["user"]);
    }

    public function getEventAccessByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->getByPK($request, $response, new EventAccessModel(), null);
    }

    public function assignEventAccessByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->updateByPK(
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

        $this->checkAdminEventPermission($request, $response);

        return $this->deleteByPK($request, $response, (new EventAccessModel()));
    }

    public function deleteEventAccessByPKs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->deleteManyByPK($request, $response, (new EventAccessModel()));
    }

    /** Organiser Staff */

    public function getOrganiserEventAccesses(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();
        $this->checkOrganiserEventPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $whereInEventTicketIds = $this->getEventTicketIdsOfOrganiser($organiser_id);

        $routeParams = $this->getRouteParams($request);
        if (isset($routeParams["event_ticket_id"]) && $routeParams["event_ticket_id"] != "-") {
            $conditions["event_ticket_id"] = $routeParams["event_ticket_id"];
            if (in_array($routeParams["event_ticket_id"], $whereInEventTicketIds)) {

                return $this->getByPage(
                    $request,
                    $response,
                    (new EventAccessModel()),
                    null,
                    $conditions,
                    ["user"]
                );
            }

            $payload = array("errorMessage" => "No access codes for this ticket yet", "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        return $this->getByPage(
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

    public function assignOrganiserEventAccessByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();
        $this->checkOrganiserEventPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $event_ticket_ids = $this->getEventTicketIdsOfOrganiser($organiser_id);

        $routeParams = $this->getRouteParams($request);

        $conditions["event_access_id"] = $routeParams["event_access_id"];

        if (!(new EventAccessModel())->where("event_access_id",  $conditions["event_access_id"])->whereIn("event_ticket_id", $event_ticket_ids)->exists()) {
            $payload = array("errorMessage" => "Access code not found", "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        return $this->updateByPK(
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
}
