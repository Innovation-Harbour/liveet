<?php

namespace Liveet\Controllers;

use Liveet\Controllers\BaseController;
use Liveet\Domain\Constants;
use Liveet\Models\EventInvitationModel;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\EventTicketUserModel;
use Liveet\Models\OrganiserStaffModel;
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

    public function uploadMediaCallback($mediaDetails, $directory, $mediaOptions): array
    {
        ["name" => $name, "ext" => $ext] = $mediaDetails;

        return ["name" => $name, "ext" => $ext];
    }


    /** Admin Permissions */

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

    public function checkAdminActivityLogPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ACTIVITY_LOG, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkAdminReportPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_REPORT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkAdminPaymentPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_PAYMENT, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkAdminUserPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_USER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkAdminFaceVerificationLogPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_FACE_VERIFICATION_LOG, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkAdminTurnstilePermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_TURNSTILE, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }


    /** Organiser Permissions */

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

    public function checkOrganiserActivityLogPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_ACTIVITY_LOG, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserReportPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_REPORT, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserPaymentPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_PAYMENT, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserUserPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_USER, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserFaceVerificationLogPermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_FACE_VERIFICATION_LOG, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function checkOrganiserTurnstilePermission(Request $request, ResponseInterface $response)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_TURNSTILE, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    /** */

    public function getOrganiserStaffIds($organiser_id)
    {
        $organiser_staff_id_s = (new OrganiserStaffModel())->select("organiser_staff_id")->where("organiser_id", $organiser_id)->get();

        $organiser_staff_ids = [];
        foreach ($organiser_staff_id_s as $organiser_staff_value) {
            $organiser_staff_ids[] = $organiser_staff_value["organiser_staff_id"];
        }

        return $organiser_staff_ids;
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

    public function organiserStaffBelongsToOrganiser($request, $response, $organiser_staff_id)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $organiser_staff = OrganiserStaffModel::find($organiser_staff_id);
        if (!$organiser_staff || $organiser_staff["organiser_id"] != $organiser_id) {
            $error = ["errorMessage" => "Organiser staff not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
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

    public function eventInvitationBelongsToOrganiser($request, $response, $event_invitation_id)
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $eventInvitation = EventInvitationModel::find($event_invitation_id);
        if (!$eventInvitation) {
            $error = ["errorMessage" => "Invitation not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        $event = $eventInvitation->event;
        if ($event["organiser_id"] != $organiser_id) {
            $error = ["errorMessage" => "Event not found", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
    }

    public function getEventCode($request)
    {
        $eventID = $this->checkOrGetPostBody($request, ["event_id"]);
        if (!$eventID) {
            return null;
        }

        $eventID = $eventID["event_id"];

        $eventID = (int)$eventID;
        $event = EventModel::find($eventID);

        $event_code = "";
        if ($event) {
            $event_code = $event["event_code"];
        } else {
            return null;
        }

        return $event_code;
    }
}
