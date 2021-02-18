<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Domain\Constants;
use Rashtell\Domain\KeyManager;

class AdminUserModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'admin_user';
    protected $dateFormat = 'U';
    protected $hidden = ["admin_password"];

    public function adminFeatures()
    {
        return $this->belongsToMany(AdminFeatureModel::class, "admin_feature_user", "admin_user_id", "admin_feature_id", "admin_user_id", "admin_feature_id");
    }

    public function adminAcitivityLogs()
    {
        return $this->hasMany(AdminActivityLogModel::class, "admin_user_id, admin_user_id");
    }

    public function getDashboard()
    {
        $adminsCount = self::count();

        $dashboard = [
            "adminsCount" => $adminsCount,
        ];

        return ["error" => "", "data" => $dashboard];
    }

    public function createSelf($details)
    {
        $inputError = $this->checkInputError($details, ["email", "phone", "username"]);
        if (null != $inputError) {
            return $inputError;
        }

        $username = $details['username'];
        $password = $details['password'];
        $name = $details['name'];
        $phone = $details['phone'];
        $email = $details['email'];
        $address = $details['address'];
        $public_key = $details['public_key'];
        $emailVerificationToken = $details['emailVerificationToken'];
        $priviledges = json_encode($details['priviledges']);

        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
        $this->public_key = $public_key;
        $this->emailVerificationToken = $emailVerificationToken;
        $this->priviledges = $priviledges;
        $this->userType = Constants::USERTYPE_ADMIN;

        $this->save();

        $id = $this->select('id')->where('username', $username)->first()['id'];

        $admin = $this->get($id);

        return ['data' => $admin['data'], 'error' => $admin['error']];
    }

    public function login($details)
    {
        $username = $details['username'];
        $password = $details['password'];
        $public_key = $details['public_key'];

        if (!(new BaseModel())->isExist($this->where('username', $username)->where('password', $password))) {
            return ['error' => 'Invalid Login credential', 'data' => null];
        }

        self::where('username', $username)->where('password', $password)->update([
            'public_key' => $public_key
        ]);

        $admin = self::select('id', 'username', 'name', 'phone', 'email', 'address', 'userType', 'phoneVerified', 'emailVerified', 'priviledges', 'public_key', 'dateCreated', 'dateUpdated')->where('username', $username)->where('public_key', $public_key)->where('password', $password)->first();

        return ['data' => $admin, 'error' => ''];
    }

    public function getStruct()
    {
        return self::select('admin_user_id', 'admin_fullname', 'admin_username', 'admin_password', 'admin_phone', 'admin_email',  'phone_verified', 'email_verified', 'created_at', 'updated_at');
    }

    public function updateSelf($details)
    {
        $inputError = $this->checkInputError($details, ["id", "email", "phone", "username"]);
        if (null != $inputError) {
            return $inputError;
        }

        $id = $details['id'];
        $username = $details['username'];
        $name = $details['name'];
        $phone = $details['phone'];
        $email = $details['email'];
        $address = $details['address'];

        $admin = $this->find($id);

        $admin->username = $username;
        $admin->name = $name;
        $admin->phone = $phone;
        $admin->email = $email;
        $admin->address = $address;

        $admin->save();

        $admin = $this->get($id);

        return ['data' => $admin['data'], 'error' => $admin['error']];
    }

    public function updateById($details)
    {
        return $this->updateSelf($details);
    }
}
