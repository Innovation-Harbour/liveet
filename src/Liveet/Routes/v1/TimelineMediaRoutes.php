<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\TimelineMediaController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/timeline-media",
            TimelineMediaController::class . ":createTimelineMedia"
        );

        $eventGroup->get(
            "/get/timeline-medias[/{timeline_id}[/{page}[/{limit}]]]",
            TimelineMediaController::class . ":getTimelineMedias"
        );

        $eventGroup->get(
            "/get/timeline-media/{timeline_media_id}",
            TimelineMediaController::class . ":getTimelineMediaByPK"
        );

        $eventGroup->put(
            "/update/timeline-media/{timeline_media_id}",
            TimelineMediaController::class . ":updateTimelineMediaByPK"
        );

        $eventGroup->delete(
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
    function (RouteCollectorProxy $eventGroup) {
    }
);
