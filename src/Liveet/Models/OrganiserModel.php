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
        $authDetails = (new BaseModel())->getTokenInputs($token);

        if ($authDetails == []) {
            return ["isAuthenticated" => false, "error" => "Invalid token"];
        }

        $public_key = $authDetails["public_key"] ?? "";
        $organiser_username = $authDetails["organiser_username"] ?? "";
        $usertype = $authDetails["usertype"] ?? "";

        $users =  self::where("public_key", $public_key)
            ->where("organiser_username", "=", $organiser_username)
            ->where("usertype", "=", $usertype)
            ->take(1)
            ->get();

        foreach ($users as $user) {
            return ($user->exists) ? ["isAuthenticated" => true, "error" => ""] : ["isAuthenticated" => false, "error" => "Expired session"];
        }

        return ["isAuthenticated" => false, "error" => "Expired session"];
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

        $organiserAdmin = (new OrganiserStaffModel())->createSelf(["organiser_id" => $organiser_id, "usertype" => Constants::USERTYPE_ORGANISER_ADMIN, "organiser_staff_name" => $organiser_name, "organiser_staff_username" => $organiser_staff_username, "organiser_staff_password" => $organiser_staff_password, "organiser_staff_email" => $organiser_email, "organiser_staff_phone" => $organiser_phone, "organiser_staff_profile_picture" => $organiser_staff_profile_picture, "email_verification_token" => $email_verification_token]);

        $organiser["data"]["admin"] = $organiserAdmin["data"];

        return ["data" => $organiser["data"], "error" => $organiser["error"]];
    }

    public function getStruct()
    {
        $pkKey = $this->primaryKey;
        return $this->select($pkKey, "organiser_username",  "organiser_name", "organiser_email", "organiser_phone", "organiser_address", "phone_verified", "usertype", "email_verified", "created_at", "updated_at");
    }

    public function updateByPK($pk, $allInputs, $checks = [])
    {
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

    public function login($details)
    {
        $organiser_username = $details["organiser_username"];
        $organiser_password = $details["organiser_password"];
        $public_key = $details["public_key"];

        $organiserStaffModel = new OrganiserStaffModel();
        if (!(new BaseModel())->isExist($this->where("organiser_username", $organiser_username)->where("organiser_password", $organiser_password))) {

            $organiserStaffQuery = $organiserStaffModel->where("organiser_staff_username", $organiser_username);
            if ($organiserStaffQuery->exists()) {

                $organiser_staff_id = $organiserStaffQuery->first()["organiser_staff_id"];

                (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "Organiser login failed"]);
            }

            return ["error" => "Invalid Login credential", "data" => null];
        }

        self::where("organiser_username", $organiser_username)->where("organiser_password", $organiser_password)->update([
            "public_key" => $public_key
        ]);

        OrganiserStaffModel::where("organiser_staff_username", $organiser_username)->where("organiser_staff_password", $organiser_password)->update([
            "public_key" => $public_key
        ]);

        $pkColumnName = $this->primaryKey;
        $organiser = self::select($pkColumnName, "organiser_username",  "organiser_name", "organiser_email", "organiser_phone", "organiser_address", "phone_verified", "usertype", "email_verified",  "public_key", "usertype", "created_at", "updated_at")->where("organiser_username", $organiser_username)->where("public_key", $public_key)->where("organiser_password", $organiser_password)->first();
        $organiser->makeVisible(["public_key"]);

        $user = $organiserStaffModel->where("organiser_username", $organiser_username)->first();

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $user["organiser_staff_id"], "activity_log_desc" => "Organiser login successful"]);

        return ["data" => $organiser, "error" => ""];
    }

    public function updateByConditions($conditions, $allInputs, $checks = [], $queryOptions = [])
    {
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
}
