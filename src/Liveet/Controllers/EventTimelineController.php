<?php

namespace Liveet\Controllers;

use Illuminate\Support\Facades\Event;
use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventTimelineModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\AdminActivityLogModel;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventTimelineController extends HelperController
{

    /** Admin User */

    public function createEventTimeline(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created an event timeline"]);

        $event_code = $this->getEventCode($request);
        if (!$event_code) {
            $error = ["errorMessage" => "Invalid event selected", "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        return $this->createSelf(
            $request,
            $response,
            new EventTimelineModel(),
            [
                "required" => [
                    "event_id",
                    "timeline_desc", "timeline_media"
                ],

                "expected" => [
                    "event_id",
                    "timeline_desc", "timeline_media"
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

    public function getEventTimelines(Request $request, ResponseInterface $response): ResponseInterface
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

        return $this->getByPage($request, $response, new EventTimelineModel(), null, $conditions, ["timelineMedia"]);
    }

    public function getEventTimelineByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, new EventTimelineModel(), null, ["timelineMedia"]);
    }

    public function updateEventTimelineByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an event timeline"]);

        return $this->updateByPK(
            $request,
            $response,
            new EventTimelineModel(),
            [
                "required" => [
                    "event_id",
                    "timeline_desc"
                ],

                "expected" => [
                    "event_id",
                    "timeline_desc"
                ]
            ]
        );
    }

    public function deleteEventTimelineByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminEventPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "deleted an event timeline"]);

        return $this->deleteByPK($request, $response, (new EventTimelineModel()));
    }


    /** Organiser Staff */

    public function getOrganiserEventTimelines(Request $request, ResponseInterface $response): ResponseInterface
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

        return $this->getByPage($request, $response, new EventTimelineModel(), null, $conditions, ["timelineMedia"]);
    }
}
