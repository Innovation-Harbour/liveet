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
        $this->checkAdminFaceVerificationLogPermission($request, $response);

        $expectedRouteParams = ["verification_log_id", "event_id", "user_id", "organiser_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-" && $value != "organiser_id") {
                $conditions[$key] = $value;
            }
        }

        $organiser_id = $routeParams["organiser_id"];

        if ($organiser_id && $organiser_id != "-") {
            return $this->getByPage(
                $request,
                $response,
                new FaceVerificationLogModel(),
                null,
                $conditions,
                ["user", "event"],
                ["whereHas" => [
                    "event" => function ($query) use ($organiser_id) {
                        return $query->where("organiser_id", $organiser_id);
                    }
                ]]
            );
        }

        return $this->getByPage($request, $response, new FaceVerificationLogModel(), null, $conditions, ["user", "event"]);
    }


    /** Organiser Staff */

    public function getOrganiserFaceVerificationLogs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkOrganiserFaceVerificationLogPermission($request, $response);

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["verification_log_id", "event_id", "user_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new FaceVerificationLogModel(), null, $conditions, ["user", "event"], [
            "whereHas" => [
                "event" => function ($query) use ($organiser_id) {
                    return $query->where("organiser_id", $organiser_id);
                }
            ]
        ]);
    }
}
