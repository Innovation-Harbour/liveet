<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventInvitationController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $eventGroup) {

        $eventGroup->post(
            "/create/invitation",
            EventInvitationController::class . ":createEventInvitation"
        );

        $eventGroup->get(
            "/get/invitations[/{event_id}[/{page}[/{limit}]]]",
            EventInvitationController::class . ":getEventInvitations"
        );

        $eventGroup->get(
            "/get/invitation/{event_invitation_id}",
            EventInvitationController::class . ":getEventInvitationByPK"
        );

        $eventGroup->put(
            "/update/invitation/{event_invitation_id}",
            EventInvitationController::class . ":updateEventInvitationByPK"
        );

        $eventGroup->delete(
            "/delete/invitation/{event_invitation_id}",
            EventInvitationController::class . ":deleteEventInvitationByPK"
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
            "/create/invitation",
            EventInvitationController::class . ":createOrganiserEventInvitation"
        );

        $eventGroup->get(
            "/get/invitations[/{event_id}[/{page}[/{limit}]]]",
            EventInvitationController::class . ":getOrganiserEventInvitations"
        );

        $eventGroup->get(
            "/get/invitation/{event_invitation_id}",
            EventInvitationController::class . ":getOrganiserEventInvitationByPK"
        );

        $eventGroup->put(
            "/update/invitation/{event_invitation_id}",
            EventInvitationController::class . ":updateOrganiserEventInvitationByPK"
        );

        $eventGroup->delete(
            "/delete/invitation/{event_invitation_id}",
            EventInvitationController::class . ":deleteOrganiserEventInvitationByPK"
        );
    }
);
