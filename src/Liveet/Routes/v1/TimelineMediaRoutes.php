<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\TimelineMediaController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $timelineMediaGroup) {

        $timelineMediaGroup->post(
            "/create/timeline-media",
            TimelineMediaController::class . ":createTimelineMedia"
        );

        $timelineMediaGroup->get(
            "/get/timeline-medias[/{timeline_media_id}[/{timeline_id}[/{event_id}[/{page}[/{limit}[/{organiser_id}]]]]]]",
            TimelineMediaController::class . ":getTimelineMedias"
        );

        $timelineMediaGroup->get(
            "/get/timeline-media/{timeline_media_id}",
            TimelineMediaController::class . ":getTimelineMediaByPK"
        );

        $timelineMediaGroup->put(
            "/update/timeline-media/{timeline_media_id}",
            TimelineMediaController::class . ":updateTimelineMediaByPK"
        );

        $timelineMediaGroup->delete(
            "/delete/timeline-media/{timeline_media_id}",
            TimelineMediaController::class . ":deleteTimelineMediaByPK"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $timelineMediaGroup) {

        $timelineMediaGroup->post(
            "/create/timeline-media",
            TimelineMediaController::class . ":organiserCreateTimelineMedia"
        );

        $timelineMediaGroup->get(
            "/get/timeline-medias[/{timeline_media_id}[/{timeline_id}[/{event_id}[/{page}[/{limit}]]]]]",
            TimelineMediaController::class . ":getOrganiserTimelineMedias"
        );


        $timelineMediaGroup->put(
            "/update/timeline-media/{timeline_media_id}",
            TimelineMediaController::class . ":organiserUpdateTimelineMediaByPK"
        );

        $timelineMediaGroup->delete(
            "/delete/timeline-media/{timeline_media_id}",
            TimelineMediaController::class . ":organiserDeleteTimelineMediaByPK"
        );
    }
);
