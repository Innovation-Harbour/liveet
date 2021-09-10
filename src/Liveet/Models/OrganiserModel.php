<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Domain\Constants;
use Rashtell\Domain\CodeLibrary;

class OrganiserModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "organiser";
    protected $dateFormat = "U";
    public $primaryKey = "organiser_id";
    // protected $with = ["organiserStaffs"];
    protected $guarded = [];
    public $passwordKey = "organiser_password";
    protected $hidden = ["organiser_password"];

    public function organiserStaffs()
    {
        return $this->hasMany(OrganiserStaffModel::class, $this->primaryKey, $this->primaryKey);
    }

    public function organiserActivityLogs()
    {
        return $this->hasManyThrough(OrganiserActivityLogModel::class, OrganiserStaffModel::class, $this->primaryKey, "organiser_staff_id", $this->primaryKey, "organiser_staff_id");
    }

    public function events()
    {
        return $this->hasMany(EventModel::class, $this->primaryKey, $this->primaryKey);
    }

    public function authenticate($token)
    {
        $authDetails = $this->getTokenInputs($token);

        if ($authDetails == []) {
            return ["isAuthenticated" => false, "error" => "Invalid token"];
        }

        $public_key = $authDetails["public_key"] ?? "";
        $organiser_username = $authDetails["organiser_username"] ?? "";
        $usertype = $authDetails["usertype"] ?? "";

        $userQuery =  $this->where("public_key", $public_key)
            ->where("organiser_username", "=", $organiser_username)
            ->where(
                "usertype",
                "=",
                $usertype
                    ->where("accessStatus", Constants::USER_ENABLED)
            );

        return ($userQuery->exists()) ? ["isAuthenticated" => true, "error" => ""] : ["isAuthenticated" => false, "error" => "Expired session"];
    }

    public function createSelf($details, $checks = [])
    {
        $inputError = $this->checkInputError($details, ["organiser_email",]);
        if (null != $inputError) {
            return $inputError;
        }

        $inputError = $this->checkInputError($details, [
            [
                "detailsKey" => "organiser_username", "columnName" => "organiser_staff_username", "errorText" =>
                "Organiser Username"
            ],
            [
                "detailsKey" => "organiser_email", "columnName" => "organiser_staff_email", "errorText" =>
                "Organiser email"
            ],
        ], new OrganiserStaffModel());
        if (null != $inputError) {
            return $inputError;
        }

        $organiser_name = $details["organiser_name"];
        $organiser_email = $details["organiser_email"];
        $organiser_phone = $details["organiser_phone"];
        $organiser_address = $details["organiser_address"];

        $organiser_staff_username = $details["organiser_username"];
        $organiser_staff_password = $details["organiser_password"];
        $organiser_staff_profile_picture = $details["organiser_profile_picture"];

        $email_verification_token = $details["email_verification_token"];

        $this->organiser_username = $organiser_staff_username;
        $this->organiser_password = $organiser_staff_password;
        $this->organiser_name = $organiser_name;
        $this->organiser_email = $organiser_email;
        $this->organiser_phone = $organiser_phone;
        $this->organiser_address = $organiser_address;
        $this->email_verification_token = $email_verification_token;

        $this->save();

        $pkKey = $this->primaryKey;
        $organiser_id = $this->select($pkKey)->where("organiser_name", $organiser_name)->first()[$this->primaryKey];

        $organiser = $this->getByPK($organiser_id);

        $organiserAdmin = (new OrganiserStaffModel())->createSelf([$this->primaryKey => $organiser_id, "usertype" => Constants::USERTYPE_ORGANISER_ADMIN, "organiser_staff_name" => $organiser_name, "organiser_staff_username" => $organiser_staff_username, "organiser_staff_password" => $organiser_staff_password, "organiser_staff_email" => $organiser_email, "organiser_staff_phone" => $organiser_phone, "organiser_staff_profile_picture" => $organiser_staff_profile_picture, "email_verification_token" => $email_verification_token]);

        $organiser["data"]["admin"] = $organiserAdmin["data"];

        return ["data" => $organiser["data"], "error" => $organiser["error"]];
    }

    public function getStruct()
    {
        $pkKey = $this->primaryKey;
        return $this->select($pkKey, "organiser_username",  "organiser_name", "organiser_email", "organiser_phone", "organiser_address", "organiser_profile_picture", "phone_verified", "usertype", "accessStatus", "email_verified", "created_at", "updated_at");
    }

    public function login($details)
    {
        $organiser_username = $details["organiser_username"];
        $organiser_password = $details["organiser_password"];
        $public_key = $details["public_key"];

        $organiserStaffModel = new OrganiserStaffModel();
        $organiserStaffQuery = $organiserStaffModel->where(function ($query) use ($organiser_username) {
            return $query->where("organiser_staff_username", $organiser_username)->orWhere("organiser_staff_email", $organiser_username);
        })->where("organiser_password", $organiser_password)
            ->where("accessStatus", Constants::USER_ENABLED);

        if (!$this->isExist($organiserStaffQuery)) {

            if ($organiserStaffQuery->exists()) {

                $organiser_staff_id = $organiserStaffQuery->first()["organiser_staff_id"];

                (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "Organiser login failed"]);
            }

            return ["error" => "Invalid Login credential", "data" => null];
        }

        $this->where(function ($query) use ($organiser_username) {
            return $query->where("organiser_username", $organiser_username)->orWhere("organiser_email", $organiser_username);
        })->where("organiser_password", $organiser_password)->update([
            "public_key" => $public_key
        ]);

        $organiserStaffQuery->update([
            "public_key" => $public_key
        ]);

        $pkColumnName = $this->primaryKey;
        $organiser = $this->select($pkColumnName, "organiser_username",  "organiser_name", "organiser_email", "organiser_phone", "organiser_address", "phone_verified", "usertype", "email_verified",  "public_key", "usertype", "created_at", "updated_at")
            ->where(function ($query) use ($organiser_username) {
                return $query->where("organiser_username", $organiser_username)
                    ->orWhere("organiser_email", $organiser_username);
            })
            ->where("public_key", $public_key)
            ->where("organiser_password", $organiser_password)
            ->first();
        $organiser->makeVisible(["public_key"]);

        $user = $organiserStaffModel->where("organiser_username", $organiser_username)->first();

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $user["organiser_staff_id"], "activity_log_desc" => "Organiser login successful"]);

        return ["data" => $organiser, "error" => ""];
    }

    public function updateByPK($pk, $allInputs, $checks = [], $queryOptions = [])
    {
        if (isset($queryOptions["useParentModel"]) && $queryOptions["useParentModel"]) {
            return parent::updateByPK($pk, $allInputs, $checks);
        }

        $inputError = $this->checkInputError($allInputs, $checks);
        if (null != $inputError) {
            return $inputError;
        }

        unset($allInputs[$this->primaryKey]);

        (new OrganiserStaffModel())->where($this->primaryKey, $pk)->update(
            ["organiser_staff_username" => $allInputs["organiser_username"], "organiser_staff_name" => $allInputs["organiser_name"], "organiser_staff_phone" => $allInputs["organiser_phone"], "organiser_staff_profile_picture" => $allInputs["organiser_profile_picture"]]
        );

        unset($allInputs["organiser_profile_picture"]);

        $query = $this->find($pk);
        if (!$query) {
            return ["error" => Constants::ERROR_NOT_FOUND, "data" => null];
        }

        $query->update($allInputs);

        $model = $this->getByPK($pk);

        return ["data" => $model["data"], "error" => $model["error"]];
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
            return ["error" => "Error while updating", "data" => null];
        }

        OrganiserStaffModel::where("usertype", Constants::USERTYPE_ORGANISER_ADMIN)->where($conditions)->update(
            ["organiser_staff_username" => $allInputs["organiser_username"], "organiser_staff_name" => $allInputs["organiser_name"], "organiser_staff_phone" => $allInputs["organiser_phone"], "organiser_staff_profile_picture" => $allInputs["organiser_profile_picture"]]
        );

        unset($allInputs["organiser_profile_picture"]);


        $query->update($allInputs);

        $model = $this->getByConditions($conditions);

        return ["data" => $model["data"], "error" => $model["error"]];
    }

    public function changeArrayKey($arr, $oldKey, $newkey)
    {
        $newArr =  $arr;

        foreach ($arr as $key => $value) {
            $changedKey = $key;


            if (gettype($key) === "string" && $key == $oldKey) {
                $newArr[$newkey] = $value;
                $changedKey = $newkey;

                unset($newArr[$oldKey]);
            }

            if (gettype($key) === "integer" && gettype($value) != "array"  && $value == $oldKey) {
                $newArr[$key] = $newkey;
            }

            if (gettype($value) === "array") {
                $newArr[$changedKey] = $this->changeArrayKey($value, $oldKey, $newkey);
            }
        }

        return $newArr;
    }

    public function getDashboard($conditions, $queryOptions = null)
    {
        $organiser_id = null;
        $event_id = null;

        foreach ($conditions as $key => $condition) {
            if ($key == "organiser_id") {
                $organiser_id = $condition;
            }

            if ($condition[0] == "organiser_id") {
                $organiser_id = $condition[2];
            }
            
            if ($key == "event_id") {
                $event_id = $condition;
            }

            if ($condition[0] == "event_id") {
                $event_id = $condition[2];
            }
        }

        $organiserCondition = $organiser_id ? ["organiser_id" => $organiser_id] : [];
        
        $staffCount = OrganiserStaffModel::where($organiserCondition)->count();

        if ($event_id) {
            $conditions =  $this->changeArrayKey($conditions, "event_id", "event.event_id");
        }

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "event.created_at");

        $eventCount = EventModel::where($newConditions)->count();
        $publicEventCount = EventModel::where($newConditions)->where("event_type", Constants::EVENT_TYPE_PUBLIC)->count();
        $privateEventCount = EventModel::where($newConditions)->where("event_type", Constants::EVENT_TYPE_PRIVATE)->count();
        $freeEventCount = EventModel::where($newConditions)->where("event_payment_type", Constants::PAYMENT_TYPE_FREE)->count();
        $paidEventCount = EventModel::where($newConditions)->where("event_payment_type", Constants::PAYMENT_TYPE_PAID)->count();

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "event_ticket.created_at");

        $eventTicketTypesCount = EventTicketModel::join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->count();

        $totalEventTicketCount = EventTicketModel::join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->sum("ticket_population");
        $totalExpectedTicketRevenue = EventTicketModel::selectRaw('SUM(ticket_population * ticket_cost) as totalExpectedTicketRevenue')
            ->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->first()["totalExpectedTicketRevenue"];

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "event_ticket_users.created_at");

        $totalBoughtTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")
            ->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->count();
        $totalBoughtTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->sum("ticket_cost");

        $totalUsedTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("status", Constants::EVENT_TICKET_USED)->count();
        $totalUsedTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("status", Constants::EVENT_TICKET_USED)->sum("ticket_cost");

        $totalUnusedTicketByTicketCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("status", Constants::EVENT_TICKET_UNUSED)->count();
        $totalUnusedTicketByTicketSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("status", Constants::EVENT_TICKET_UNUSED)->sum("ticket_cost");

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "event_access.created_at");

        $totalGeneratedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->count();
        $totalGeneratedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->sum("ticket_cost");

        $totalAssignedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->count();
        $totalAssignedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->sum("ticket_cost");

        $totalUnassignedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->count();
        $totalUnassignedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->sum("ticket_cost");

        $totalUsedAccessCodeCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("event_access_used_status", Constants::EVENT_ACCESS_USED)->count();
        $totalUsedAccessCodeSum = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->where("event_access_used_status", Constants::EVENT_ACCESS_USED)->sum("ticket_cost");

        $totalPredictedUsedTicketCount = $totalBoughtTicketByTicketCount + $totalUsedAccessCodeCount + $totalAssignedAccessCodeCount;
        $totalPreredictedRevenue = $totalBoughtTicketByTicketSum + $totalUsedAccessCodeSum + $totalAssignedAccessCodeSum;

        $totalMinimumUsedTicketCount = $totalUsedTicketByTicketCount + $totalUsedAccessCodeCount;
        $totalMinimumPossibleRevenue = $totalUsedTicketByTicketSum + $totalUsedAccessCodeSum;

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "event_invitation.created_at");

        $totalGeneratedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalGeneratedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($newConditions)->first()["totalGeneratedInvitations"];
        $totalAcceptedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalAcceptedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($newConditions)->where("event_invitation_status", Constants::INVITATION_ACCEPT)->first()["totalAcceptedInvitations"];
        $totalPendingInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalPendingInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($newConditions)->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalPendingInvitations"];
        $totalRejectedInvitations = EventInvitationModel::selectRaw('SUM(invitee_can_invite_count)  as totalRejectedInvitations')->join("event", "event.event_id", "=", "event_invitation.event_id")->where($newConditions)->where("event_invitation_status", Constants::INVITATION_PENDING)->first()["totalRejectedInvitations"];

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "event_timeline.created_at");

        $eventTimelinesCount = EventTimelineModel::join("event", "event.event_id", "=", "event_timeline.event_id")->where($newConditions)->count();

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "timeline_media.created_at");

        $evnetTimelinMediaCount = TimelineMediaModel::join("event_timeline", "event_timeline.timeline_id", "=", "timeline_media.timeline_id")->join("event", "event.event_id", "=", "event_timeline.event_id")->where($newConditions)->count();

        $newConditions =  $this->changeArrayKey($conditions, "created_at", "payment.created_at");

        $paymentCount = PaymentModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "payment.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->count();
        $paymentSum = PaymentModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "payment.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where($newConditions)->sum("ticket_cost");

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
