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

        $user =  self::where("public_key", $public_key)
            // ->where("token", "=", $token)
            ->first();

        return ($user && $user->exists) ? ["isAuthenticated" => true, "error" => ""] : ["isAuthenticated" => false, "error" => "Expired session"];
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
        })->where("organiser_password", $organiser_staff_password);

        if (!$this->isExist($organiserStaffQuery)) {

            if ($organiserStaffQuery->exists()) {

                $organiser_staff_id = $organiserStaffQuery->first()["organiser_staff_id"];

                (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "Organiser login failed"]);
            }

            return ["error" => "Invalid Login credential", "data" => null];
        }

        $organiserStaffQuery->update([
            "public_key" => $public_key
        ]);

        $pkKey = $this->primaryKey;
        $organiserStaff = self::select($pkKey, "organiser_id", "organiser_staff_name", "organiser_staff_username", "organiser_staff_phone", "organiser_staff_email", "organiser_staff_profile_picture", "organiser_staff_priviledges", "phone_verified", "email_verified", "usertype", "public_key", "created_at", "updated_at")
            ->where(function ($query) use ($organiser_staff_username) {
                return $query->where("organiser_staff_username", $organiser_staff_username)
                    ->orWhere("organiser_email", $organiser_staff_username);
            })
            ->where("organiser_staff_password", $organiser_staff_password)
            ->first();
        $organiserStaff->makeVisible(["public_key"]);

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiserStaff["organiser_staff_id"], "activity_log_desc" => "Organiser login successful"]);

        return ["data" => $organiserStaff, "error" => ""];
    }

    public function getDashboard($pk, $queryOptions = null, $extras = null)
    {
        $organiserStaff =  $this->where($this->primaryKey, $pk)->first();
        $organiser_id = $organiserStaff["organiser_id"];

        $staffCount = $this->where("organiser_id", $organiser_id)->where("usertype", Constants::USERTYPE_ORGANISER_STAFF)->count();

        $eventCount = EventModel::where("organiser_id", $organiser_id)->count();

        $eventTicketUserCount = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->count();

        $eventTickerAccessCount = EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->count();

        $eventTicketUserSum = EventTicketUserModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_ticket_users.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->sum("ticket_cost");

        $eventTickerAccessSum =  EventAccessModel::join("event_ticket", "event_ticket.event_ticket_id", "=", "event_access.event_ticket_id")->join("event", "event.event_id", "=", "event_ticket.event_id")->where("organiser_id", $organiser_id)->sum("ticket_cost");

        $dashboard = [
            "staffCount" => $staffCount,
            "eventCount" => $eventCount,
            "eventTicketUserCount" => $eventTicketUserCount,
            "eventTickerAccessCount" => $eventTickerAccessCount,
            "eventTicketUserSum" => $eventTicketUserSum,
            "eventTicketAccessSum" => $eventTickerAccessSum,
            "totalTickets" => $eventTicketUserSum + $eventTickerAccessSum,
        ];

        return ["error" => "", "data" => $dashboard];
    }

    public function getStruct()
    {
        $pkKey = $this->primaryKey;
        return $this->select($pkKey, "organiser_id", "organiser_staff_name", "organiser_staff_username", "organiser_staff_phone", "organiser_staff_email", "organiser_staff_profile_picture", "organiser_staff_priviledges", "phone_verified", "email_verified", "usertype", "created_at", "updated_at");
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
        };

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
