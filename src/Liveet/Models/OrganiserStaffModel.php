<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Domain\Constants;
use Rashtell\Domain\CodeLibrary;

class OrganiserStaffModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "organiser_staff";
    protected $dateFormat = "U";
    public $primaryKey = "organiser_staff_id";
    protected $hidden = ["organiser_staff_password", "email_verification_token", "forgot_password_token", "public_key", "deleted_at"];
    protected $guarded = [];
    public $passwordKey = "organiser_staff_password";

    public function organiser()
    {
        return $this->belongsTo(OrganiserModel::class, "organiser_id", "organiser_id");
    }

    public function organiserActivityLogs()
    {
        return $this->hasMany(OrganiserActivityLogModel::class, $this->primaryKey, $this->primaryKey);
    }

    public function authenticate($token)
    {
        $authDetails = $this->getTokenInputs($token);

        if ($authDetails == []) {
            return ["isAuthenticated" => false, "error" => "Invalid token"];
        }

        $public_key = $authDetails["public_key"];
        $organiser_id = $authDetails["organiser_id"];

        $organiser = OrganiserModel::find($organiser_id);
        $organiserAccess = $organiser ? $organiser["accessStatus"] : null;
        $organiserAccessEnabled = $organiserAccess == Constants::USER_ENABLED;

        $userQuery =  $this->where("public_key", $public_key)
            ->where("organiser_id", $organiser_id)
            ->where("accessStatus", Constants::USER_ENABLED)
            // ->where("token", "=", $token)
        ;

        return ($userQuery->exists() && $organiserAccessEnabled) ? ["isAuthenticated" => true, "error" => ""] : ["isAuthenticated" => false, "error" => "Expired session"];
    }

    public function authenticateWithPublicKey($details)
    {
        $public_key = $details["public_key"];

        $user = $this->where(["public_key" => $public_key])->exists();
        if (!$user) {
            return ["data" => null, "error" => "Invalid credential"];
        }

        // $user = $this->find(["public_key" => $public_key]);
        // if (!$user or sizeof($user) == 0) {
        //     return ["data" => null, "error" => "Invalid credential"];
        // }

        return ["data" => $this->getStruct()->where("public_key", $public_key)->first(), "error" => null];
    }

    public function generateNewPublicKey($details)
    {
        $pk = $details[$this->primaryKey];
        $cLib = new CodeLibrary();
        $public_key = $cLib->genID(40, 1);

        $user = $this->find($pk);

        if (!$user) {
            return ["data" => null, "error" => "Organization not found"];
        }

        $user->public_key = $public_key;
        $user->save();

        return ["data" => ["public_key" => $public_key], "error" => null];
    }

    public function login($details)
    {
        $organiser_staff_username = $details["organiser_staff_username"];
        $organiser_staff_password = $details["organiser_staff_password"];
        $public_key = $details["public_key"];

        $organiserStaffQuery = $this->where(function ($query) use ($organiser_staff_username) {
            return $query->where("organiser_staff_username", $organiser_staff_username)->orWhere("organiser_staff_email", $organiser_staff_username);
        })->where("organiser_staff_password", $organiser_staff_password)
            ->where("accessStatus", Constants::USER_ENABLED);

        if (!$this->isExist($organiserStaffQuery)) {

            if ($organiserStaffQuery->exists()) {

                $organiser_staff_id = $organiserStaffQuery->first()[$this->primaryKey];

                (new OrganiserActivityLogModel())->createSelf([$this->primaryKey => $organiser_staff_id, "activity_log_desc" => "Organiser login failed"]);
            }

            return ["error" => "Invalid Login credential", "data" => null];
        }

        $organiserStaffQuery->update([
            "public_key" => $public_key
        ]);

        $pkKey = $this->primaryKey;
        $organiserStaff = $this->select($pkKey, "organiser_id", "organiser_staff_name", "organiser_staff_username", "organiser_staff_phone", "organiser_staff_email", "organiser_staff_profile_picture", "organiser_staff_priviledges", "phone_verified", "email_verified", "usertype", "public_key", "created_at", "updated_at")
            ->where(function ($query) use ($organiser_staff_username) {
                return $query->where("organiser_staff_username", $organiser_staff_username)
                    ->orWhere("organiser_staff_email", $organiser_staff_username);
            })
            ->where("organiser_staff_password", $organiser_staff_password)
            ->first();
        $organiserStaff->makeVisible(["public_key"]);

        (new OrganiserActivityLogModel())->createSelf([$this->primaryKey => $organiserStaff[$this->primaryKey], "activity_log_desc" => "Organiser login successful"]);

        return ["data" => $organiserStaff, "error" => ""];
    }

    public function getDashboard($pk, $queryOptions = null, $extras = null)
    {
        // $organiserStaff =  $this->where($this->primaryKey, $pk)->first();
        // $organiser_id = $organiserStaff["organiser_id"];
        $organiser_id = $pk;

        $staffCount = $this->where("organiser_id", $organiser_id)->where("usertype", Constants::USERTYPE_ORGANISER_STAFF)->count();

        $eventCount = EventModel::where("organiser_id", $organiser_id)->count();
        $publicEventCount = EventModel::where("organiser_id", $organiser_id)->where("event_type", Constants::EVENT_TYPE_PUBLIC)->count();
        $privateEventCount = EventModel::where("organiser_id", $organiser_id)->where("event_type", Constants::EVENT_TYPE_PRIVATE)->count();
        $freeEventCount = EventModel::where("organiser_id", $organiser_id)->where("event_payment_type", Constants::PAYMENT_TYPE_FREE)->count();
        $paidEventCount = EventModel::where("organiser_id", $organiser_id)->where("event_payment_type", Constants::PAYMENT_TYPE_PAID)->count();

        $eventTicketTypesCount = EventTicketModel::join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->count();

        $totalEventTicketCount = EventTicketModel::join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->sum("ticket_population");
        $totalExpectedTicketRevenue = EventTicketModel::selectRaw('SUM(ticket_population * ticket_cost) as totalExpectedTicketRevenue')
            ->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->first()["totalExpectedTicketRevenue"];

        $totalBoughtTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")
            ->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->count();
        $totalBoughtTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->sum("ticket_cost");

        $totalUsedTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("status", Constants::EVENT_TICKET_USED)->count();
        $totalUsedTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("status", Constants::EVENT_TICKET_USED)->sum("ticket_cost");

        $totalUnusedTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("status", Constants::EVENT_TICKET_UNUSED)->count();
        $totalUnusedTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("status", Constants::EVENT_TICKET_UNUSED)->sum("ticket_cost");

        $totalGeneratedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->count();
        $totalGeneratedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->sum("ticket_cost");

        $totalAssignedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->count();
        $totalAssignedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->sum("ticket_cost");

        $totalUnassignedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->count();
        $totalUnassignedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->sum("ticket_cost");

        $totalUsedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("event_access_used_status", Constants::EVENT_ACCESS_USED)->count();
        $totalUsedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->where("event_access_used_status", Constants::EVENT_ACCESS_USED)->sum("ticket_cost");

        $totalPredictedUsedTicketCount = $totalBoughtTicketByTicketCount + $totalUsedAccessCodeCount + $totalAssignedAccessCodeCount;
        $totalPreredictedRevenue = $totalBoughtTicketByTicketSum + $totalUsedAccessCodeSum + $totalAssignedAccessCodeSum;

        $totalMinimumUsedTicketCount = $totalUsedTicketByTicketCount + $totalUsedAccessCodeCount;
        $totalMinimumPossibleRevenue = $totalUsedTicketByTicketSum + $totalUsedAccessCodeSum;

        $totalGeneratedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalGeneratedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where("organiser_id", $organiser_id)->first()["totalGeneratedInvitations"];
        $totalAcceptedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalAcceptedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where("organiser_id", $organiser_id)->where("event_invitation_status", Constants::INVITATION_ACCEPT)->first()["totalAcceptedInvitations"];
        $totalPendingInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalPendingInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where("organiser_id", $organiser_id)->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalPendingInvitations"];
        $totalRejectedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalRejectedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where("organiser_id", $organiser_id)->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalRejectedInvitations"];

        $eventTimelinesCount = EventTimelineModel::join("event", "event.event_id", "=", "event_timeline.event_id")->where("organiser_id", $organiser_id)->count();
        $evnetTimelinMediaCount = TimelineMediaModel::join("event_timeline", "event_timeline.timeline_id", "=", "timeline_media.timeline_id")->join("event", "event.event_id", "=", "event_timeline.event_id")->where("organiser_id", $organiser_id)->count();

        $paymentCount = PaymentModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "payment.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->count();
        $paymentSum = PaymentModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "payment.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->sum("ticket_cost");

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

    public function getStruct()
    {
        $pkKey = $this->primaryKey;
        return $this->select($pkKey, "organiser_id", "organiser_staff_name", "organiser_staff_username", "organiser_staff_phone", "organiser_staff_email", "organiser_staff_profile_picture", "organiser_staff_priviledges", "phone_verified", "email_verified", "usertype", "accessStatus", "created_at", "updated_at");
    }

    public function updateByConditions($conditions, $allInputs, $checks = [], $queryOptions = [])
    {
        if (isset($queryOptions["useParentModel"]) && $queryOptions["useParentModel"]) {
            return parent::updateByConditions($conditions, $allInputs, $checks, $queryOptions);
        }

        $inputError = $this->checkInputError($allInputs, $checks);
        if (null != $inputError) {
            return $inputError;
        }

        $query = $this->where($conditions);
        if (!$query->exists()) {
            return ["error" => "Error while updating ( Invalid inputs )", "data" => null];
        }

        $this->where("usertype", Constants::USERTYPE_ORGANISER_ADMIN)->where($conditions)->update(
            ["organiser_staff_username" => $allInputs["organiser_staff_username"], "organiser_staff_name" => $allInputs["organiser_staff_name"], "organiser_staff_phone" => $allInputs["organiser_staff_phone"], "organiser_staff_profile_picture" => $allInputs["organiser_staff_profile_picture"]]
        );

        OrganiserModel::where("organiser_id", $allInputs["organiser_id"])->update(
            ["organiser_username" => $allInputs["organiser_staff_username"], "organiser_name" => $allInputs["organiser_staff_name"], "organiser_phone" => $allInputs["organiser_staff_phone"], "organiser_address" => $allInputs["organiser_staff_address"]]
        );

        $model = $this->getByConditions($conditions);

        return ["data" => $model["data"], "error" => $model["error"]];
    }
}
