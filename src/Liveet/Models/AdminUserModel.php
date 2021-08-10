<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Event;
use Liveet\Domain\Constants;
use Rashtell\Domain\KeyManager;

class AdminUserModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "admin_user";
    protected $dateFormat = "U";
    protected $hidden = [
        "admin_password",
        "public_key",
        "email_verification_token", "forgot_password_token", "deleted_at"
    ];
    public $primaryKey = "admin_user_id";
    protected $guarded = [];
    public $passwordKey = "admin_password";

    public function adminFeatures()
    {
        return $this->belongsToMany(AdminFeatureModel::class, "admin_feature_user", $this->primaryKey, "admin_feature_id", $this->primaryKey, "admin_feature_id");
    }

    public function adminAcitivityLogs()
    {
        return $this->hasMany(AdminActivityLogModel::class, "admin_user_id, admin_user_id");
    }

    public function authenticate($token)
    {
        $authDetails = $this->getTokenInputs($token);

        if ($authDetails == []) {
            return ["isAuthenticated" => false, "error" => "Invalid token"];
        }

        $public_key = $authDetails["public_key"] ?? "";
        $admin_username = $authDetails["admin_username"] ?? "";
        $usertype = $authDetails["usertype"] ?? "";

        $user =  $this::where("public_key", $public_key)
            ->where("admin_username", "=", $admin_username)
            ->where("usertype", "=", $usertype)
            ->where("accessStatus", Constants::USER_ENABLED)
            ->first();

        return ($user->exists) ? ["isAuthenticated" => true, "error" => ""] : ["isAuthenticated" => false, "error" => "Expired session"];
    }

    public function getDashboard($pk, $queryOptions = null, $extras = null)
    {
        $adminsCount = self::count();
        $organiserCount = (new OrganiserModel())->where("usertype", Constants::USERTYPE_ORGANISER_ADMIN)->count();
        $organiserStaffCount = (new OrganiserModel())->where("usertype", Constants::USERTYPE_ORGANISER_STAFF)->count();
        $usersCount = UserModel::count();

        $eventCount = EventModel::count();
        $publicEventCount = EventModel::where("event_type", Constants::EVENT_TYPE_PUBLIC)->count();
        $privateEventCount = EventModel::where("event_type", Constants::EVENT_TYPE_PRIVATE)->count();
        $freeEventCount = EventModel::where("event_payment_type", Constants::PAYMENT_TYPE_FREE)->count();
        $paidEventCount = EventModel::where("event_payment_type", Constants::PAYMENT_TYPE_PAID)->count();

        $eventTicketTypesCount = EventTicketModel::count();


        $totalEventTicketCount = EventTicketModel::sum("ticket_population");
        $totalExpectedTicketRevenue = EventTicketModel::selectRaw('SUM(ticket_population * ticket_cost) as totalExpectedTicketRevenue')->first()["totalExpectedTicketRevenue"];

        $totalBoughtTicketByTicketCount = EventTicketUserModel::count();
        $totalBoughtTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->sum("ticket_cost");

        $totalUsedTicketByTicketCount = EventTicketUserModel::where("status", Constants::EVENT_TICKET_USED)->count();
        $totalUsedTicketByTicketSum = EventTicketUserModel::where("status", Constants::EVENT_TICKET_USED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->sum("ticket_cost");

        $totalUnusedTicketByTicketCount = EventTicketUserModel::where("status", Constants::EVENT_TICKET_UNUSED)->count();
        $totalUnusedTicketByTicketSum = EventTicketUserModel::where("status", Constants::EVENT_TICKET_UNUSED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->sum("ticket_cost");

        $totalGeneratedAccessCodeCount = EventAccessModel::count();
        $totalGeneratedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");

        $totalAssignedAccessCodeCount = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->count();
        $totalAssignedAccessCodeSum = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");

        $totalUnassignedAccessCodeCount = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->count();
        $totalUnassignedAccessCodeSum = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");

        $totalUsedAccessCodeCount = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_USED)->count();
        $totalUsedAccessCodeSum = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_USED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");

        $totalPredictedUsedTicketCount = $totalBoughtTicketByTicketCount + $totalUsedAccessCodeCount + $totalAssignedAccessCodeCount;
        $totalPreredictedRevenue = $totalBoughtTicketByTicketSum + $totalUsedAccessCodeSum + $totalAssignedAccessCodeSum;

        $totalMinimumUsedTicketCount = $totalUsedTicketByTicketCount + $totalUsedAccessCodeCount;
        $totalMinimumPossibleRevenue = $totalUsedTicketByTicketSum + $totalUsedAccessCodeSum;

        $totalGeneratedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalGeneratedInvitations')->first()["totalGeneratedInvitations"];
        $totalAcceptedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalAcceptedInvitations')->where("event_invitation_status", Constants::INVITATION_ACCEPT)->first()["totalAcceptedInvitations"];
        $totalPendingInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalPendingInvitations')->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalPendingInvitations"];
        $totalRejectedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalRejectedInvitations')->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalRejectedInvitations"];

        $eventTimelinesCount = EventTimelineModel::count();
        $evnetTimelinMediaCount = TimelineMediaModel::count();

        $paymentCount = PaymentModel::count();
        $paymentSum = PaymentModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "payment.event_ticket_id")->sum("ticket_cost");


        $dashboard = [
            "adminsCount" => $adminsCount ?? 0,
            "organiserCount" => $organiserCount ?? 0,
            "organiserStaffCount" => $organiserStaffCount ?? 0,
            "usersCount" => $usersCount ?? 0,

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

    public function createSelf($details, $checks = [])
    {
        $inputError = $this->checkInputError($details, ["admin_username", "admin_email",]);
        if (null != $inputError) {
            return $inputError;
        }

        $admin_username = $details["admin_username"];
        $admin_password = $details["admin_password"];
        $admin_fullname = $details["admin_fullname"];
        $admin_email = $details["admin_email"];
        $public_key = $details["public_key"];
        $email_verification_token = $details["email_verification_token"];
        $admin_priviledges = json_encode($details["admin_priviledges"]);

        $this->admin_username = $admin_username;
        $this->admin_password = $admin_password;
        $this->admin_fullname = $admin_fullname;
        $this->admin_email = $admin_email;
        $this->public_key = $public_key;
        $this->email_verification_token = $email_verification_token;
        $this->admin_priviledges = $admin_priviledges;
        $this->usertype = Constants::USERTYPE_ADMIN;

        $this->save();

        $pkKey = $this->primaryKey;
        $admin_user_id = $this->select($pkKey)->where("admin_username", $admin_username)->first()[$this->primaryKey];

        $admin = $this->getByPK($admin_user_id, null, null, ["idKey" => $this->primaryKey]);

        return ["data" => $admin["data"], "error" => $admin["error"]];
    }

    public function login($details)
    {
        $admin_username = $details["admin_username"];
        $admin_password = $details["admin_password"];
        $public_key = $details["public_key"];

        $adminQuery = $this->where(function ($query) use ($admin_username) {
            return $query->where("admin_username", $admin_username)->orWhere("admin_email", $admin_username);
        })->where("admin_password", $admin_password)
            ->where("accessStatus", Constants::USER_ENABLED);

        if (!$this->isExist($adminQuery)) {

            if ($adminQuery->exists()) {

                $admin_user_id = $adminQuery->first()["admin_user_id"];

                (new AdminActivityLogModel())->createSelf(["admin_user_id" => $admin_user_id, "activity_log_desc" => "Admin login failed"]);
            }

            return ["error" => "Invalid Login credential", "data" => null];
        }

        $adminQuery->update([
            "public_key" => $public_key
        ]);

        $pkColumnName = $this->primaryKey;
        $admin = $this->select($pkColumnName, "admin_fullname", "admin_username", "admin_email", "admin_priviledges", "email_verified",  "public_key", "usertype", "created_at", "updated_at")
            ->where(function ($query) use ($admin_username) {
                return $query->where("admin_username", $admin_username)->orWhere("admin_email", $admin_username);
            })
            ->where("public_key", $public_key)
            ->where("admin_password", $admin_password)
            ->first();

        $admin->makeVisible(["public_key"]);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $admin["admin_user_id"], "activity_log_desc" => "Admin login successfully"]);

        return ["data" => $admin, "error" => ""];
    }

    public function getStruct()
    {
        $pkKey = $this->primaryKey;
        return self::select($pkKey, "admin_fullname", "admin_username", "admin_password", "admin_email", "admin_priviledges", "email_verified",  "usertype", "accessStatus", "created_at", "updated_at");
    }
}
