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
            ->where("usertype", "=", $usertype)->first();

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

        $totalBoughtTicketByTicketCount = EventTicketUserModel::count();
        $totalUsedTicketByTicketCount = EventTicketUserModel::where("status", Constants::EVENT_TICKET_USED)->count();
        $totalUnusedTicketByTicketCount = EventTicketUserModel::where("status", Constants::EVENT_TICKET_UNUSED)->count();

        $totalGeneratedAccessCodeCount = EventAccessModel::count();
        $totalAssignedAccessCodeCount = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->count();
        $totalUnassignedAccessCodeCount = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->count();
        $totalUsedAccessCodeCount = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_USED)->count();

        $totalPredictedUsedTicketCount = $totalBoughtTicketByTicketCount + $totalUsedAccessCodeCount + $totalAssignedAccessCodeCount;
        $totalMinimumUsedTicketCount = $totalUsedTicketByTicketCount + $totalUsedAccessCodeCount;


        $totalExpectedTicketRevenue = EventTicketModel::selectRaw('SUM(ticket_population * ticket_cost) as totalExpectedTicketRevenue')->first()["totalExpectedTicketRevenue"];

        $totalBoughtTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->sum("ticket_cost");
        $totalUsedTicketByTicketSum = EventTicketUserModel::where("status", Constants::EVENT_TICKET_USED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->sum("ticket_cost");
        $totalUnusedTicketByTicketSum = EventTicketUserModel::where("status", Constants::EVENT_TICKET_UNUSED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->sum("ticket_cost");

        $totalGeneratedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");
        $totalAssignedAccessCodeSum = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");;
        $totalUnassignedAccessCodeSum = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");;
        $totalUsedAccessCodeSum = EventAccessModel::where("event_access_used_status", Constants::EVENT_ACCESS_USED)->join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->sum("ticket_cost");;

        $totalPreredictedRevenue = $totalBoughtTicketByTicketSum + $totalUsedAccessCodeSum + $totalAssignedAccessCodeSum;
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
            "adminsCount" => $adminsCount,
            "organiserCount" => $organiserCount,
            "organiserStaffCount" => $organiserStaffCount,
            "usersCount" => $usersCount,

            "eventCount" => $eventCount,
            "publicEventCount" => $publicEventCount,
            "privateEventCount" => $privateEventCount,
            "freeEventCount" => $freeEventCount,
            "paidEventCount" => $paidEventCount,

            "eventTicketTypesCount" => $eventTicketTypesCount,

            "totalEventTicketCount" => $totalEventTicketCount,
            "totalExpectedTicketRevenue" => $totalExpectedTicketRevenue,

            "totalBoughtTicketByTicketCount" => $totalBoughtTicketByTicketCount,
            "totalBoughtTicketByTicketSum" => $totalBoughtTicketByTicketSum,

            "totalUsedTicketByTicketCount" => $totalUsedTicketByTicketCount,
            "totalUsedTicketByTicketSum" => $totalUsedTicketByTicketSum,

            "totalUnusedTicketByTicketCount" => $totalUnusedTicketByTicketCount,
            "totalUnusedTicketByTicketSum" => $totalUnusedTicketByTicketSum,

            "totalGeneratedAccessCodeCount" => $totalGeneratedAccessCodeCount,
            "totalGeneratedAccessCodeSum" => $totalGeneratedAccessCodeSum,

            "totalAssignedAccessCodeCount" => $totalAssignedAccessCodeCount,
            "totalAssignedAccessCodeSum" => $totalAssignedAccessCodeSum,

            "totalUnassignedAccessCodeCount" => $totalUnassignedAccessCodeCount,
            "totalUnassignedAccessCodeSum" => $totalUnassignedAccessCodeSum,

            "totalUsedAccessCodeCount" => $totalUsedAccessCodeCount,
            "totalUsedAccessCodeSum" => $totalUsedAccessCodeSum,

            "totalPredictedUsedTicketCount" => $totalPredictedUsedTicketCount,
            "totalPreredictedRevenue" => $totalPreredictedRevenue,

            "totalMinimumUsedTicketCount" => $totalMinimumUsedTicketCount,
            "totalMinimumPossibleRevenue" => $totalMinimumPossibleRevenue,


            "totalGeneratedInvitations" => $totalGeneratedInvitations,
            "totalAcceptedInvitations" => $totalAcceptedInvitations,
            "totalPendingInvitations" => $totalPendingInvitations,
            "totalRejectedInvitations" => $totalRejectedInvitations,


            "eventTicketUserCount" => $totalEventTicketCount,
            "eventTicketUserSum" => $totalBoughtTicketByTicketSum,

            "eventTickerAccessCount" => $totalGeneratedAccessCodeCount,
            "eventTicketAccessCount" => $totalGeneratedAccessCodeCount,
            "eventTicketAccessSum" => $totalGeneratedAccessCodeSum,

            "totalTicketCount" => $totalEventTicketCount + $totalGeneratedAccessCodeCount,
            "totalTicketSum" => $totalBoughtTicketByTicketSum + $totalGeneratedAccessCodeSum,

            "eventTimelinesCount" => $eventTimelinesCount,
            "evnetTimelinMediaCount" => $evnetTimelinMediaCount,

            "paymentCount" => $paymentCount,
            "paymentSum" => $paymentSum

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
        })->where("admin_password", $admin_password);

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
        return self::select($pkKey, "admin_fullname", "admin_username", "admin_password", "admin_email", "admin_priviledges", "email_verified",  "usertype", "created_at", "updated_at");
    }
}
