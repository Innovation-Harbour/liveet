<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventInvitationModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\AdminActivityLogModel;
use Liveet\Models\EventModel;
use Liveet\Models\OrganiserActivityLogModel;
use Liveet\Models\OrganiserStaffModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventInvitationController extends HelperController
{

    /** Admin User */

    public function createEventInvitation(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created an event invitation"]);

        return $this->createSelf(
            $request,
            $response,
            new EventInvitationModel(),
            [
                "required" => [
                    "event_id", "event_invitee_user_phone"
                ],

                "expected" => [
                    "event_id", "invitation_name", "event_inviter_user_id", "event_invitee_user_phone", "invitee_can_invite_count"

                ]
            ]
        );
    }

    public function getEventInvitations(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["event_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new EventInvitationModel(), null, $conditions);
    }

    public function getEventInvitationByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, new EventInvitationModel(), null);
    }

    public function updateEventInvitationByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an event invitation"]);

        return $this->updateByPK(
            $request,
            $response,
            new EventInvitationModel(),
            [
                "required" => [
                    "event_invitee_user_phone"
                ],

                "expected" => [
                    "invitation_name", "event_invitee_user_phone", "invitee_can_invite_count"

                ]
            ]
        );
    }

    public function deleteEventInvitationByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "deleted an event invitation"]);

        return $this->deleteByPK($request, $response, (new EventInvitationModel()));
    }

    /** Organiser Staff */

    public function createOrganiserEventInvitation(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "created an event invitation"]);

        $postBody = $this->checkOrGetPostBody($request, ["event_id"]);
        $event_id = $postBody["event_id"];

        $this->eventBelongsToOrganiser($request, $response, $event_id);

        return $this->createSelf(
            $request,
            $response,
            new EventInvitationModel(),
            [
                "required" => [
                    "event_id", "event_invitee_user_phone"
                ],

                "expected" => [
                    "event_id", "invitation_name", "event_inviter_user_id", "event_invitee_user_phone", "invitee_can_invite_count"

                ]
            ]
        );
    }

    public function getOrganiserEventInvitations(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }
        
        $authDetails = static::getTokenInputsFromRequest($request);

        $expectedRouteParams = ["event_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        if (isset($conditions["event_id"])) {
            $this->eventBelongsToOrganiser($request, $response, $conditions["event_id"]);

            return $this->getByPage($request, $response, new EventInvitationModel(), null, $conditions);
        }

        $organiser_id = $authDetails["organiser_id"];
        $event_ids = $this->getEventIdsOfOrganiser($organiser_id);

        return $this->getByPage($request, $response, new EventInvitationModel(), null, null, null, ["whereIn" => [["event_id" => $event_ids]]]);
    }

    public function getOrganiserEventInvitationByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $routeParams = $this->getRouteParams($request, ["event_invitation_id"]);
        if (isset($routeParams["event_invitation_id"])) {
            $this->eventInvitationBelongsToOrganiser($request, $response, $routeParams["event_invitation_id"]);
        }
        return $this->getByPK($request, $response, (new EventInvitationModel()));
    }

    public function updateOrganiserEventInvitationByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "updated an event invitation"]);

        $routeParams = $this->getRouteParams($request, ["event_invitation_id"]);
        if (isset($routeParams["event_invitation_id"])) {
            $this->eventInvitationBelongsToOrganiser($request, $response, $routeParams["event_invitation_id"]);
        }

        return $this->updateByPK(
            $request,
            $response,
            new EventInvitationModel(),
            [
                "required" => [
                    "event_invitee_user_phone"
                ],
                "expected" => [
                    "invitation_name", "event_invitee_user_phone", "invitee_can_invite_count"
                ]
            ]
        );
    }

    public function deleteOrganiserEventInvitationByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "deleted an event invitation"]);

        ["event_invitation_id" => $event_invitation_id] = $this->getRouteParams($request, ["event_invitation_id"]);

        if (isset($event_invitation_id)) {
            $this->eventInvitationBelongsToOrganiser($request, $response, $event_invitation_id);
        }

        $data = (new EventInvitationModel())->deleteByPK($event_invitation_id);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Delete success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }
}
