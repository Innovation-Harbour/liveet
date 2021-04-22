<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventTimelineModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventTimelineController extends HelperController
{

    /** Admin User */

    public function createEventTimeline(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

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
                "imageOptions" => [
                    ["imageKey" => "timeline_media", "multiple" => true]
                ]
            ],
        );
    }

    public function getEventTimelines(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

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
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->getByPK($request, $response, new EventTimelineModel(), null, ["timelineMedia"]);
    }

    public function updateEventTimelineByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

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
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->deleteByPK($request, $response, (new EventTimelineModel()));
    }



    /** Organiser Staff */

    public function getOrganiserEventTimelines(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkOrganiserEventPermission($request, $response);
        $organiser_id = $authDetails["organiser_id"];
        $expectedRouteParams = ["event_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = ["organiser_id" => $organiser_id];

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
