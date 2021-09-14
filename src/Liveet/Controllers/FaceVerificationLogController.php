<?php

namespace Liveet\Controllers;

use Liveet\Models\FaceVerificationLogModel;
use Liveet\Models\ActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class FaceVerificationLogController extends HelperController
{

    /** Admin User */

    public function getFaceVerificationLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminFaceVerificationLogPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["verification_log_id", "event_id", "user_phone", "organiser_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-" && $value != "organiser_id") {
                $conditions[$key] = $value;
            }
        }

        $whereHas = [];
        if (isset($conditions["organiser_id"])) {
            $organiser_id = $conditions["organiser_id"];

            $whereHas["event"] = function ($query) use ($organiser_id) {
                return $query->where("organiser_id", $organiser_id);
            };

            unset($conditions["organiser_id"]);
        }

        if (isset($conditions["user_phone"])) {
            $user_phone = $conditions["user_phone"];

            $whereHas["user"] = function ($query) use ($user_phone) {
                return $query->where("user_phone", "LIKE", "%$user_phone%");
            };

            unset($conditions["user_phone"]);
        }

        return $this->getByPage($request, $response, new FaceVerificationLogModel(), null, $conditions, ["user", "event"]);
    }


    /** Organiser Staff */

    public function getOrganiserFaceVerificationLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserFaceVerificationLogPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["verification_log_id", "event_id", "user_phone"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas["event"] = function ($query) use ($organiser_id) {
            return $query->where("organiser_id", $organiser_id);
        };

        if (isset($conditions["user_phone"])) {
            $user_phone = $conditions["user_phone"];

            $whereHas["user"] = function ($query) use ($user_phone) {
                return $query->where("user_phone", "LIKE", "%$user_phone%");
            };

            unset($conditions["user_phone"]);
        }

        return $this->getByPage($request, $response, new FaceVerificationLogModel(), null, $conditions, ["user", "event"], ["whereHas" => $whereHas]);
    }
}
