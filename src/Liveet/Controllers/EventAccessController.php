<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventAccessModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\EventModel;
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
                    "event_id", "event_ticket_id", "event_access_population"
                ],

                "expected" => [
                    "event_id", "event_ticket_id", "event_access_population"

                ],
            ]
        );
    }

    public function getEventAccesss(Request $request, ResponseInterface $response): ResponseInterface
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

        return (new BaseController)->getByPage($request, $response, new EventAccessModel(), null, $conditions);
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

    public function updateEventAccessByPK(Request $request, ResponseInterface $response): ResponseInterface
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
                    "access_name", "access_desc", "access_cost", "access_population"
                ],

                "expected" => [
                    "event_access_id", "access_name", "access_desc", "access_cost", "access_population", "access_discount"
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

    /** Organiser Staff */

    public function getOrganiserEventAccesss(Request $request, ResponseInterface $response): ResponseInterface
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

        $event_ids = (new EventModel())->select("event_id")->where("organiser_id", $organiser_id)->without("eventControl", "eventAccesss")->get();

        $whereInEventIds = [];
        $i = 0;
        foreach ($event_ids as $event_id_value) {
            $whereInEventIds[$i] = $event_id_value["event_id"];
            $i++;
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
                    ["event_id" => $whereInEventIds],
                ]
            ]
        );
    }
}
