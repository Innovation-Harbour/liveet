<?php

namespace Liveet\Controllers;

use Liveet\Controllers\BaseController;
use Liveet\Domain\Constants;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\EventTicketUserModel;
use Rashtell\Domain\JSON;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelperController extends BaseController
{
    public function uploadMedias($request, $response)
    {
        $json =  new JSON();
        $uploadedFileDetails = Parent::handleUploadMedias($request);

        $payload = array("successMessage" => "Request success", "statusCode" => 200, "data" => $uploadedFileDetails);

        return $json->withJsonResponse($response, $payload);
    }
    public function checkAdminAdminPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserAdminPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $usertype = $authDetails["usertype"];
        if ($usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkAdminOrganiserPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserOrganiserPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_staff_priviledges"]) && gettype($authDetails["organiser_staff_priviledges"]) == "array" ? json_decode($authDetails["organiser_staff_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_ORGANISER, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkAdminEventPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_EVENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserEventPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_EVENT, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function getEventIdsOfOrganiser($organiser_id)
    {
        $event_id_s = (new EventModel())->select("event_id")->where("organiser_id", $organiser_id)->without("eventControl", "eventTickets")->get();

        $event_ids = [];
        foreach ($event_id_s as $event_id_value) {
            $event_ids[] = $event_id_value["event_id"];
        }

        return $event_ids;
    }

    public function getEventTicketIdsOfOrganiser($organiser_id)
    {
        $event_ids = $this->getEventIdsOfOrganiser($organiser_id);

        return $this->getEventTicketIds($event_ids);
    }

    public function getEventTicketIds($event_ids)
    {
        $event_ticket_id_s = (new EventTicketModel())->select("event_ticket_id")->whereIn("event_id", $event_ids)->get();

        $eventTicketIds = [];
        foreach ($event_ticket_id_s as $event_ticket_id_value) {
            $eventTicketIds[] = $event_ticket_id_value["event_ticket_id"];
        }

        return $eventTicketIds;
    }

    public function eventBelongsToOrganiser($request, $response, $event_id)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $event = EventModel::find($event_id);
        if (!$event || $event["organiser_id"] != $organiser_id) {
            $error = ["errorMessage" => "Event not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function eventTicketBelongsToOrganiser($request, $response, $event_ticket_id)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $eventTicket = EventTicketModel::find($event_ticket_id);
        if (!$eventTicket) {
            $error = ["errorMessage" => "Event ticket not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
        $event = $eventTicket->event;
        if ($event["organiser_id"] != $organiser_id) {
            $error = ["errorMessage" => "Event ticket not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function eventTicketUserBelongsToOrganiser($request, $response, $event_ticket_user_id)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $eventTicketUser = EventTicketUserModel::find($event_ticket_user_id);
        if (!$eventTicketUser) {
            $error = ["errorMessage" => "Ticket not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        $event = $eventTicketUser->eventTicket->event;
        if ($event["organiser_id"] != $organiser_id) {
            $error = ["errorMessage" => "Ticket not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }
}
