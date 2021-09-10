<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventTimelineController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $timelineGroup) {

        $timelineGroup->post(
            "/create/timeline",
            EventTimelineController::class . ":createEventTimeline"
        );

        $timelineGroup->get(
            "/get/timelines[/{event_id}[/{page}[/{limit}[/{organiser_id}[/{timeline_id}]]]]]",
            EventTimelineController::class . ":getEventTimelines"
        );

        $timelineGroup->get(
            "/get/timeline/{timeline_id}",
            EventTimelineController::class . ":getEventTimelineByPK"
        );

        $timelineGroup->put(
            "/update/timeline/{timeline_id}",
            EventTimelineController::class . ":updateEventTimelineByPK"
        );

        $timelineGroup->delete(
            "/delete/timeline/{timeline_id}",
            EventTimelineController::class . ":deleteEventTimelineByPK"
        );
    }
);

/**
 * Organiser Priviledged
 */
isset($organiserStaffGroup) && $organiserStaffGroup->group(
    "",
    function (RouteCollectorProxy $timelineGroup) {

        $timelineGroup->post(
            "/create/timeline",
            EventTimelineController::class . ":organiserCreateEventTimeline"
        );

        $timelineGroup->get(
            "/get/timelines[/{event_id}[/{page}[/{limit}[/{timeline_id}]]]]",
            EventTimelineController::class . ":getOrganiserEventTimelines"
        );

        $timelineGroup->put(
            "/update/timeline/{timeline_id}",
            EventTimelineController::class . ":organiserUpdateEventTimelineByPK"
        );

        $timelineGroup->delete(
            "/delete/timeline/{timeline_id}",
            EventTimelineController::class . ":organiserDeleteEventTimelineByPK"
        );
    }
);
