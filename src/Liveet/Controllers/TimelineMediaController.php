<?php

namespace Liveet\Controllers;

use Liveet\Models\AdminActivityLogModel;
use Liveet\Models\OrganiserActivityLogModel;
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

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "added event timeline media"]);

        $event_code = $this->getEventCodeByTimeline($request);
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
                            "containerName" => "liveet-prod-media", "mediaName" => $event_code . "-" . rand(00000000, 99999999)
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

        $expectedRouteParams = ["timeline_id", "event_id"];
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

            $whereHas["eventTimeline"] = function ($query) use ($event_id) {
                return $query->where("event_id", $event_id);
            };

            unset($conditions["event_id"]);
        }

        return $this->getByPage($request, $response, new TimelineMediaModel(), null, $conditions, null, ["whereHas" => $whereHas]);
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

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an event timeline media"]);

        $event_code = $this->getEventCodeByTimeline($request);
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
                            "containerName" => "liveet-prod-media", "mediaName" => $event_code . "-" . rand(00000000, 99999999)
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

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "deleted an event timeline media"]);

        return $this->deleteByPK($request, $response, (new TimelineMediaModel()));
    }


    /** Organiser Staff */

    public function organiserCreateTimelineMedia(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $eventTimelineDoesNotBelongsToOrganiser = $this->eventTimelineBelongsToOrganiser($request, $response);
        if ($eventTimelineDoesNotBelongsToOrganiser) {
            return $eventTimelineDoesNotBelongsToOrganiser;
        }

        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $organiser_staff_id = $authDetails["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "added event timeline media"]);

        $event_code = $this->getEventCodeByTimeline($request);
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
                            "containerName" => "liveet-prod-media", "mediaName" => $event_code . "-" . rand(00000000, 99999999)
                        ]
                    ]
                ]
            ]
        );
    }

    public function getOrganiserTimelineMedias(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_staff_id = $authDetails["organiser_staff_id"];
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["timeline_media_id", "timeline_id", "event_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas["eventTimeline"] = function ($query) use ($organiser_id) {
            return $query->whereHas("event", function ($query) use ($organiser_id) {
                return $query->where("organiser_id", $organiser_id);
            });
        };

        if (isset($conditions["event_id"])) {
            $event_id = $conditions["event_id"];

            $whereHas["eventTimeline"] = function ($query) use ($event_id) {
                return $query->where("event_id", $event_id);
            };

            unset($conditions["event_id"]);
        }

        return $this->getByPage($request, $response, new TimelineMediaModel(), null, $conditions, null, ["whereHas" => $whereHas]);
    }

    public function organiserUpdateTimelineMediaByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $eventTimelineDoesNotBelongsToOrganiser = $this->eventTimelineBelongsToOrganiser($request, $response);
        if ($eventTimelineDoesNotBelongsToOrganiser) {
            return $eventTimelineDoesNotBelongsToOrganiser;
        }

        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $organiser_staff_id = $authDetails["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "updated an event timeline media"]);

        $event_code = $this->getEventCodeByTimeline($request);
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
                            "containerName" => "liveet-prod-media", "mediaName" => $event_code . "-" . rand(00000000, 99999999)
                        ]
                    ]
                ]
            ]
        );
    }

    public function organiserDeleteTimelineMediaByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $eventTimelineMediaDoesNotBelongsToOrganiser = $this->eventTimelineMediaBelongsToOrganiser($request, $response);
        if ($eventTimelineMediaDoesNotBelongsToOrganiser) {
            return $eventTimelineMediaDoesNotBelongsToOrganiser;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $organiser_staff_id = $authDetails["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "deleted an event timeline media"]);

        return $this->deleteByPK($request, $response, (new TimelineMediaModel()));
    }
}
