<?php

namespace Liveet\Controllers;

use Illuminate\Support\Facades\Event;
use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\TimelineMediaModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Liveet\Models\EventTicketModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class TimelineMediaController extends HelperController
{

    /** Admin User */

    public function createTimelineMedia(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

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
                "imageOptions" => [
                    ["imageKey" => "timeline_media", "multiple" => true]
                ]
            ],
        );
    }

    public function getTimelineMedias(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

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
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->getByPK($request, $response, new TimelineMediaModel());
    }

    public function updateTimelineMediaByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

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
                "imageOptions" => [
                    ["imageKey" => "timeline_media"]
                ]
            ]
        );
    }

    public function deleteTimelineMediaByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminEventPermission($request, $response);

        return $this->deleteByPK($request, $response, (new TimelineMediaModel()));
    }


    /** Organiser Staff */

    public function getOrganiserTimelineMedias(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkOrganiserEventPermission($request, $response);
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
