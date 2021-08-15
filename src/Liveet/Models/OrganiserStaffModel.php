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

    public function getDashboard($conditions, $queryOptions = null)
    {
        $organiser_staff_id = $conditions["organiser_staff_id"];
        $organiser_id = $this->find($organiser_staff_id)["organiser_id"];

        return (new OrganiserModel())->getDashboard(["organiser_id" => $organiser_id], $queryOptions);
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
