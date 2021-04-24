<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventTimelineController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/timeline",
            EventTimelineController::class . ":createEventTimeline"
        );

        $eventGroup->get(
            "/get/timelines[/{event_id}[/{page}[/{limit}]]]",
            EventTimelineController::class . ":getEventTimelines"
        );

        $eventGroup->get(
            "/get/timeline/{timeline_id}",
            EventTimelineController::class . ":getEventTimelineByPK"
        );

        $eventGroup->put(
            "/update/timeline/{timeline_id}",
            EventTimelineController::class . ":updateEventTimelineByPK"
        );

        $eventGroup->delete(
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
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->get(
            "/get/timelines[/{event_id}[/{page}[/{limit}]]]",
            EventTimelineController::class . ":getOrganiserEventTimelines"
        );
    }
);
