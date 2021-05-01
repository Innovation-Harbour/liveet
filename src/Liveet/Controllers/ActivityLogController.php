<?php

namespace Liveet\Controllers;

use Liveet\Models\ActivityLogModel;
use Liveet\Models\AdminActivityLogModel;
use Liveet\Models\OrganiserActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class ActivityLogController extends HelperController
{

    /** Admin User */

    public function getSelfActivityLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        // $this->checkAdminActivityLogPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $admin_user_id = $authDetails["admin_user_id"];

        $conditions = ["admin_user_id" => $admin_user_id];

        return $this->getByDate($request, $response, new AdminActivityLogModel(), null, $conditions, ["adminUser"], ["dateCreatedColumn" => "created_at"]);
    }

    public function getActivityLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkAdminActivityLogPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);

        $expectedRouteParams = ["admin_user_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByDate($request, $response, new AdminActivityLogModel(), null, $conditions, ["adminUser"], ["dateCreatedColumn" => "created_at"]);
    }

    public function getOrganiserActivityLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkAdminActivityLogPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);

        $expectedRouteParams = ["organiser_id", "organiser_staff_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereIns = [];
        if (isset($conditions["organiser_id"])) {
            $organiser_ids = $this->getOrganiserStaffIds($conditions["organiser_id"]);
            unset($conditions["organiser_id"]);
            $whereIn[] = ["organiser_staff_id" => $organiser_ids];
        }

        return $this->getByDate($request, $response, new OrganiserActivityLogModel(), null, $conditions, ["organiserStaff"], ["dateCreatedColumn" => "created_at", "whereIn" => $whereIns]);
    }


    /** Organiser Staff */

    public function getSelfOrganiserStaffActivityLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        // $this->checkOrganiserActivityLogPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_staff_id = $authDetails["organiser_staff_id"];
        $conditions = ["organiser_staff_id" => $organiser_staff_id];

        return $this->getByDate($request, $response, new OrganiserActivityLogModel(), null, $conditions, ["organiserStaff"]);
    }

    public function getOrganiserStaffActivityLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkOrganiserActivityLogPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $organiser_staff_ids = $this->getOrganiserStaffIds($organiser_id);

        $expectedRouteParams = ["organiser_staff_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        if (isset($conditions["organiser_staff_id"])) {
            $this->organiserStaffBelongsToOrganiser($request, $response, $conditions["organiser_staff_id"]);
        }

        return $this->getByDate($request, $response, new OrganiserActivityLogModel(), null, $conditions, ["organiserStaff"], ["whereIn" => [["organiser_staff_id" => $organiser_staff_ids]], "dateCreatedColumn" => "created_at"]);
    }
}
