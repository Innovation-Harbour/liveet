<?php

namespace Liveet\Controllers;

use Liveet\Models\TimelineMediaModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;

class TimelineMediaController extends HelperController
{

    /** Admin User */

    public function createTimelineMedia(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $json = new JSON();

        $event_code = $this->getEventCode($request);
        if (!$event_code) {
            $error = ["errorMessage" => "Invalid event selected", "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        return $this->createSelf(
            $request,
            $response,
            new TimelineMediaModel(),
            [
                "required" => [
                    "timeline_id", "timeline_media"
                ],

                "expected" => [
                    "timeline_id", "timeline_media"
                ],
            ],
            [
                "mediaOptions" => [
                    [
                        "mediaKey" => "timeline_media", "multiple" => true, "folder" => "timelines/$event_code",
                        "clientOptions" => [
                            "containerName" => "liveet-media", "mediaName" => $event_code . "-" . rand(00000000, 99999999)
                        ]
                    ]
                ]
            ],
        );
    }

    public function getTimelineMedias(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["timeline_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new TimelineMediaModel(), null, $conditions);
    }

    public function getTimelineMediaByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, new TimelineMediaModel());
    }

    public function updateTimelineMediaByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $json = new JSON();

        $event_code = $this->getEventCode($request);
        if (!$event_code) {
            $error = ["errorMessage" => "Invalid event selected", "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        return $this->updateByPK(
            $request,
            $response,
            new TimelineMediaModel(),
            [
                "required" => [
                    "timeline_id",
                    "timeline_media"
                ],

                "expected" => [
                    "timeline_id",
                    "timeline_media", "timeline_mediaType"
                ]
            ],
            [
                "mediaOptions" => [
                    [
                        "mediaKey" => "timeline_media", "folder" => "timelines/$event_code",
                        "clientOptions" => [
                            "containerName" => "liveet-media", "mediaName" => $event_code . "-" . rand(00000000, 99999999)
                        ]
                    ]
                ]
            ]
        );
    }

    public function deleteTimelineMediaByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->deleteByPK($request, $response, (new TimelineMediaModel()));
    }


    /** Organiser Staff */

    public function getOrganiserTimelineMedias(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
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

        if (isset($conditions["event_id"])) {
            $this->eventBelongsToOrganiser($request, $response, $conditions["event_id"]);
        }

        return $this->getByPage($request, $response, new TimelineMediaModel(), null, $conditions, ["timelineMedia"]);
    }
}
