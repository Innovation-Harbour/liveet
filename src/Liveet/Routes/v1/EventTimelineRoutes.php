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

        $eventGroup->post(
            "/create/timeline",
            EventTimelineController::class . ":createOrganiserEventTimeline"
        );

        $eventGroup->get(
            "/get/timelines[/{event_id}[/{page}[/{limit}]]]",
            EventTimelineController::class . ":getOrganiserEventTimelines"
        );

        $eventGroup->get(
            "/get/timeline/{event_ticket_user_id}",
            EventTimelineController::class . ":getOrganiserEventTimelineByPK"
        );

        $eventGroup->put(
            "/transfer/timeline/{event_ticket_user_id}",
            EventTimelineController::class . ":transferOrganiserEventTimelineByPK"
        );

        $eventGroup->delete(
            "/recall/timeline/{event_ticket_user_id}",
            EventTimelineController::class . ":recallOrganiserEventTimeline"
        );
    }
);
