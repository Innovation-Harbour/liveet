<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventTicketUserModel;
use Liveet\Controllers\BaseController;
use Liveet\Models\AdminActivityLogModel;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\OrganiserActivityLogModel;
use Liveet\Models\OrganiserStaffModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventTicketUserController extends HelperController
{

    /** Admin User */

    public function createEventTicketUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "assigned an event ticket to a user"]);


        return $this->createSelf(
            $request,
            $response,
            new EventTicketUserModel(),
            [
                "required" => [
                    "event_ticket_id", "user_id"
                ],

                "expected" => [
                    "event_ticket_id", "user_id", "user_face_id"

                ],
            ]
        );
    }

    public function getEventTicketUsers(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["event_id", "event_ticket_id", "from", "to", "event_ticket_user_id", "organiser_id", "user_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas = [];
        if (isset($conditions["event_id"])) {
            $event_id = $conditions["event_id"];

            $whereHas["eventTicket"] = function ($query) use ($event_id) {
                return $query->where("event_id", $event_id);
            };

            unset($conditions["event_id"]);
        }

        if (isset($conditions["organiser_id"])) {
            $organiser_id = $conditions["organiser_id"];

            $whereHas["eventTicket"] = function ($query) use ($organiser_id) {
                return $query->whereHas("event", function ($query) use ($organiser_id) {
                    return $query->where("organiser_id", $organiser_id);
                });
            };

            unset($conditions["organiser_id"]);
        }

        if (isset($conditions["user_id"])) {
            $user_id = $conditions["user_id"];

            $whereHas["user"] = function ($query) use ($user_id) {
                return $query->where("user_id", $user_id);
            };

            unset($conditions["user_id"]);
        }


        return $this->getByPage($request, $response, new EventTicketUserModel(), null, $conditions, ["user", "eventTicket"], ["whereHas" => $whereHas]);
    }

    public function getEventTicketUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, (new EventTicketUserModel()), null, ["user", "eventTicket"]);
    }

    public function transferEventTicketUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "transfered an event ticket to a user"]);

        return $this->updateByPK(
            $request,
            $response,
            new EventTicketUserModel(),
            [
                "required" => [
                    "user_phone"
                ],

                "expected" => [
                    "user_phone", "user_face_id"
                ]
            ]
        );
    }

    public function recallEventTicketUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "recalled an event ticket from a user"]);

        ["event_ticket_user_id" => $event_ticket_user_id] = $this->getRouteParams($request, ["event_ticket_user_id"]);

        $data = (new EventTicketUserModel())->recallEventTicket($event_ticket_user_id);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Delete success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }


    /** Organiser Staff */

    public function createOrganiserEventTicketUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "assigned an event ticket to a user"]);


        $postBody = $this->checkOrGetPostBody($request, ["event_ticket_id"]);
        $event_ticket_id = $postBody["event_ticket_id"];

        $this->eventTicketBelongsToOrganiser($request, $response, $event_ticket_id);

        return $this->createSelf(
            $request,
            $response,
            new EventTicketUserModel(),
            [
                "required" => [
                    "event_ticket_id", "user_id"
                ],

                "expected" => [
                    "event_ticket_id", "user_id", "user_face_id"

                ],
            ]
        );
    }

    public function getOrganiserEventTicketUsers(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["event_id", "event_ticket_id", "from", "to", "event_ticket_user_id", "organiser_id", "user_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas["eventTicket"] = function ($query) use ($organiser_id) {
            return $query->whereHas("event", function ($query) use ($organiser_id) {
                return $query->where("organiser_id", $organiser_id);
            });
        };

        if (isset($conditions["event_id"])) {
            $event_id = $conditions["event_id"];

            $whereHas["eventTicket"] = function ($query) use ($event_id) {
                return $query->where("event_id", $event_id);
            };

            unset($conditions["event_id"]);
        }

        if (isset($conditions["user_id"])) {
            $user_id = $conditions["user_id"];

            $whereHas["user"] = function ($query) use ($user_id) {
                return $query->where("user_id", $user_id);
            };

            unset($conditions["user_id"]);
        }

        return $this->getByPage($request, $response, new EventTicketUserModel(), null, $conditions, ["user", "eventTicket"], ["whereHas" => $whereHas]);
    }

    public function getOrganiserEventTicketUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $routeParams = $this->getRouteParams($request, ["event_ticket_user_id"]);
        if (isset($routeParams["event_ticket_user_id"])) {
            $this->eventTicketUserBelongsToOrganiser($request, $response, $routeParams["event_ticket_user_id"]);
        }
        return $this->getByPK($request, $response, (new EventTicketUserModel()), null, ["user", "eventTicket"]);
    }

    public function transferOrganiserEventTicketUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "transfered an event ticket to a user"]);

        $routeParams = $this->getRouteParams($request, ["event_ticket_user_id"]);
        if (isset($routeParams["event_ticket_user_id"])) {
            $this->eventTicketUserBelongsToOrganiser($request, $response, $routeParams["event_ticket_user_id"]);
        }

        return $this->updateByPK(
            $request,
            $response,
            new EventTicketUserModel(),
            [
                "required" => [
                    "user_phone"
                ],

                "expected" => [
                    "user_phone", "user_face_id"
                ]
            ]
        );
    }

    public function recallOrganiserEventTicketUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "recalled an event ticket from a user"]);


        ["event_ticket_user_id" => $event_ticket_user_id] = $this->getRouteParams($request, ["event_ticket_user_id"]);

        if (isset($event_ticket_user_id)) {
            $this->eventTicketUserBelongsToOrganiser($request, $response, $event_ticket_user_id);
        }

        $data = (new EventTicketUserModel())->recallEventTicket($event_ticket_user_id);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Delete success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }
}
