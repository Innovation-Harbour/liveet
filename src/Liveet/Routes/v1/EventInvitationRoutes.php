<?php

use Slim\Routing\RouteCollectorProxy;

use Liveet\Controllers\EventInvitationController;


/**
 * Admin User Priviledged
 */
isset($adminGroup) && $adminGroup->group(
    "",
    function (RouteCollectorProxy $invitationGroup) {

        $invitationGroup->post(
            "/create/invitation",
            EventInvitationController::class . ":createEventInvitation"
        );

        $invitationGroup->get(
            "/get/invitations[/{event_id}[/{page}[/{limit}]]]",
            EventInvitationController::class . ":getEventInvitations"
        );

        $invitationGroup->get(
            "/get/invitation/{event_invitation_id}",
            EventInvitationController::class . ":getEventInvitationByPK"
        );

        $invitationGroup->put(
            "/update/invitation/{event_invitation_id}",
            EventInvitationController::class . ":updateEventInvitationByPK"
        );

        $invitationGroup->delete(
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
    function (RouteCollectorProxy $invitationGroup) {

        $invitationGroup->post(
            "/create/invitation",
            EventInvitationController::class . ":createOrganiserEventInvitation"
        );

        $invitationGroup->get(
            "/get/invitations[/{event_id}[/{page}[/{limit}]]]",
            EventInvitationController::class . ":getOrganiserEventInvitations"
        );

        $invitationGroup->get(
            "/get/invitation/{event_invitation_id}",
            EventInvitationController::class . ":getOrganiserEventInvitationByPK"
        );

        $invitationGroup->put(
            "/update/invitation/{event_invitation_id}",
            EventInvitationController::class . ":updateOrganiserEventInvitationByPK"
        );

        $invitationGroup->delete(
            "/delete/invitation/{event_invitation_id}",
            EventInvitationController::class . ":deleteOrganiserEventInvitationByPK"
        );
    }
);
