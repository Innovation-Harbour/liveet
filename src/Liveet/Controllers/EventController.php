<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\EventModel;

class EventController extends BaseController
{

    /** Organiser Staff */

    public function getOrganiserEvents(Request $request, ResponseInterface $response): ResponseInterface
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

        return (new BaseController)->getByPage($request, $response, new EventModel(), null, ["organiser_id" => $organiser_id], ["eventTickets", "eventControl"]);
    }

    /** Admin User */

    public function createEvent(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];

        return (new BaseController)->createSelf(
            $request,
            $response,
            new EventModel(),
            [
                "required" => [
                    "feature_name", "feature_url"
                ],

                "expected" => [
                    "event_name", "event_desc", "event_multimedia", "event_type", "event_venue", "event_date_time", "event_payment_type",

                    "tickets" => [
                        "ticket_name", "ticket_desc", "ticket_cost", "ticket_population", "ticket_discount",
                    ],

                    "event_can_invite", "event_sale_stop_time", "event_can_transfer_ticket", "event_can_recall",
                ],
            ],
            [
                "imageOptions" => [
                    ["imageKey" => "event_multimedia"]
                ]
            ],
            ["organiser_id" => $organiser_id]
        );
    }

    public function getEvents(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPage($request, $response, new EventModel(), null, null, ["eventTickets", "eventControl"]);
    }

    public function getEventByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPK($request, $response, new EventModel(), null, ["eventTickets", "eventControl"]);
    }

    public function updateEventByPK(Request $request, ResponseInterface $response): ResponseInterface
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
            new EventModel(),
            [
                "required" => [
                    "feature_name", "feature_url"
                ],

                "expected" => [
                    "feature_name",  "feature_url",
                ],
            ]
        );
    }
}
