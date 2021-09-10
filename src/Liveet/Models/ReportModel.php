<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Domain\Constants;
use Liveet\Models\BaseModel;

class ReportModel extends BaseModel
{

    public function getDashboard($conditions, $queryOptions = null)
    {
        $organiser_id = $conditions["organiser_id"];
        $event_id = $conditions["event_id"] ?? null;


        $staffCount = OrganiserStaffModel::where(["organiser_id" => $organiser_id])->count();

        $eventCount = EventModel::where($conditions)->count();
        $publicEventCount = EventModel::where($conditions)->where("event_type", Constants::EVENT_TYPE_PUBLIC)->count();
        $privateEventCount = EventModel::where($conditions)->where("event_type", Constants::EVENT_TYPE_PRIVATE)->count();
        $freeEventCount = EventModel::where($conditions)->where("event_payment_type", Constants::PAYMENT_TYPE_FREE)->count();
        $paidEventCount = EventModel::where($conditions)->where("event_payment_type", Constants::PAYMENT_TYPE_PAID)->count();

        $eventTicketTypesCount = EventTicketModel::join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->count();

        $totalEventTicketCount = EventTicketModel::join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->sum("ticket_population");
        $totalExpectedTicketRevenue = EventTicketModel::selectRaw('SUM(ticket_population * ticket_cost) as totalExpectedTicketRevenue')
            ->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->first()["totalExpectedTicketRevenue"];

        $totalBoughtTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")
            ->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->count();
        $totalBoughtTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->sum("ticket_cost");

        $totalUsedTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("status", Constants::EVENT_TICKET_USED)->count();
        $totalUsedTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("status", Constants::EVENT_TICKET_USED)->sum("ticket_cost");

        $totalUnusedTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("status", Constants::EVENT_TICKET_UNUSED)->count();
        $totalUnusedTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("status", Constants::EVENT_TICKET_UNUSED)->sum("ticket_cost");

        $totalGeneratedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->count();
        $totalGeneratedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->sum("ticket_cost");

        $totalAssignedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->count();
        $totalAssignedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->sum("ticket_cost");

        $totalUnassignedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->count();
        $totalUnassignedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->sum("ticket_cost");

        $totalUsedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("event_access_used_status", Constants::EVENT_ACCESS_USED)->count();
        $totalUsedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->where("event_access_used_status", Constants::EVENT_ACCESS_USED)->sum("ticket_cost");

        $totalPredictedUsedTicketCount = $totalBoughtTicketByTicketCount + $totalUsedAccessCodeCount + $totalAssignedAccessCodeCount;
        $totalPreredictedRevenue = $totalBoughtTicketByTicketSum + $totalUsedAccessCodeSum + $totalAssignedAccessCodeSum;

        $totalMinimumUsedTicketCount = $totalUsedTicketByTicketCount + $totalUsedAccessCodeCount;
        $totalMinimumPossibleRevenue = $totalUsedTicketByTicketSum + $totalUsedAccessCodeSum;

        $totalGeneratedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalGeneratedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($conditions)->first()["totalGeneratedInvitations"];
        $totalAcceptedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalAcceptedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($conditions)->where("event_invitation_status", Constants::INVITATION_ACCEPT)->first()["totalAcceptedInvitations"];
        $totalPendingInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalPendingInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($conditions)->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalPendingInvitations"];
        $totalRejectedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalRejectedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($conditions)->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalRejectedInvitations"];

        $eventTimelinesCount = EventTimelineModel::join("event", "event.event_id", "=", "event_timeline.event_id")->where($conditions)->count();
        $evnetTimelinMediaCount = TimelineMediaModel::join("event_timeline", "event_timeline.timeline_id", "=", "timeline_media.timeline_id")->join("event", "event.event_id", "=", "event_timeline.event_id")->where($conditions)->count();

        $paymentCount = PaymentModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "payment.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->count();
        $paymentSum = PaymentModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "payment.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($conditions)->sum("ticket_cost");

        $dashboard = [
            "staffCount" => $staffCount ?? 0,

            "eventCount" => $eventCount ?? 0,
            "publicEventCount" => $publicEventCount ?? 0,
            "privateEventCount" => $privateEventCount ?? 0,
            "freeEventCount" => $freeEventCount ?? 0,
            "paidEventCount" => $paidEventCount ?? 0,

            "eventTicketTypesCount" => $eventTicketTypesCount ?? 0,

            "totalEventTicketCount" => $totalEventTicketCount ?? 0,
            "totalExpectedTicketRevenue" => $totalExpectedTicketRevenue ?? 0,

            "totalBoughtTicketByTicketCount" => $totalBoughtTicketByTicketCount ?? 0,
            "totalBoughtTicketByTicketSum" => $totalBoughtTicketByTicketSum ?? 0,

            "totalUsedTicketByTicketCount" => $totalUsedTicketByTicketCount ?? 0,
            "totalUsedTicketByTicketSum" => $totalUsedTicketByTicketSum ?? 0,

            "totalUnusedTicketByTicketCount" => $totalUnusedTicketByTicketCount ?? 0,
            "totalUnusedTicketByTicketSum" => $totalUnusedTicketByTicketSum ?? 0,

            "totalGeneratedAccessCodeCount" => $totalGeneratedAccessCodeCount ?? 0,
            "totalGeneratedAccessCodeSum" => $totalGeneratedAccessCodeSum ?? 0,

            "totalAssignedAccessCodeCount" => $totalAssignedAccessCodeCount ?? 0,
            "totalAssignedAccessCodeSum" => $totalAssignedAccessCodeSum ?? 0,

            "totalUnassignedAccessCodeCount" => $totalUnassignedAccessCodeCount ?? 0,
            "totalUnassignedAccessCodeSum" => $totalUnassignedAccessCodeSum ?? 0,

            "totalUsedAccessCodeCount" => $totalUsedAccessCodeCount ?? 0,
            "totalUsedAccessCodeSum" => $totalUsedAccessCodeSum ?? 0,

            "totalPredictedUsedTicketCount" => $totalPredictedUsedTicketCount ?? 0,
            "totalPreredictedRevenue" => $totalPreredictedRevenue ?? 0,

            "totalMinimumUsedTicketCount" => $totalMinimumUsedTicketCount ?? 0,
            "totalMinimumPossibleRevenue" => $totalMinimumPossibleRevenue ?? 0,


            "totalGeneratedInvitations" => $totalGeneratedInvitations ?? 0,
            "totalAcceptedInvitations" => $totalAcceptedInvitations ?? 0,
            "totalPendingInvitations" => $totalPendingInvitations ?? 0,
            "totalRejectedInvitations" => $totalRejectedInvitations ?? 0,

            "eventTicketUserCount" => $totalEventTicketCount ?? 0,
            "eventTicketUserSum" => $totalBoughtTicketByTicketSum ?? 0,

            "eventTickerAccessCount" => $totalGeneratedAccessCodeCount ?? 0,
            "eventTicketAccessCount" => $totalGeneratedAccessCodeCount ?? 0,
            "eventTicketAccessSum" => $totalGeneratedAccessCodeSum ?? 0,

            "totalTicketCount" => ($totalEventTicketCount + $totalGeneratedAccessCodeCount) ?? 0,
            "totalTicketSum" => ($totalBoughtTicketByTicketSum + $totalGeneratedAccessCodeSum) ?? 0,

            "eventTimelinesCount" => $eventTimelinesCount ?? 0,
            "evnetTimelinMediaCount" => $evnetTimelinMediaCount ?? 0,

            "paymentCount" => $paymentCount ?? 0,
            "paymentSum" => $paymentSum ?? 0
        ];

        return ["error" => "", "data" => $dashboard];
    }
}
