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
    protected $primaryKey = 'admin_user_id';

    public function adminFeatures()
    {
        return $this->belongsToMany(AdminFeatureModel::class, "admin_feature_user", "admin_user_id", "admin_feature_id", "admin_user_id", "admin_feature_id");
    }

    public function adminAcitivityLogs()
    {
        return $this->hasMany(AdminActivityLogModel::class, "admin_user_id, admin_user_id");
    }

    public function authenticate($token)
    {
        $authDetails = (new BaseModel())->getTokenInputs($token);

        if ($authDetails == []) {
            return ['isAuthenticated' => false, 'error' => 'Invalid token'];
        }

        $public_key = $authDetails['public_key'];
        $admin_username = $authDetails['admin_username'];
        $usertype = $authDetails['usertype'];

        $users =  self::where('public_key', $public_key)
            ->where('admin_username', '=', $admin_username)
            ->where('usertype', '=', $usertype)
            ->take(1)
            ->get();

        foreach ($users as $user) {
            return ($user->exists) ? ['isAuthenticated' => true, 'error' => ''] : ['isAuthenticated' => false, 'error' => 'Expired session'];
        }

        return ['isAuthenticated' => false, 'error' => 'Expired session'];
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
        $inputError = $this->checkInputError($details, ["admin_username", "admin_email",]);
        if (null != $inputError) {
            return $inputError;
        }

        $admin_username = $details['admin_username'];
        $admin_password = $details['admin_password'];
        $admin_fullname = $details['admin_fullname'];
        $admin_email = $details['admin_email'];
        $public_key = $details['public_key'];
        $email_verification_token = $details['email_verification_token'];
        $admin_priviledges = json_encode($details['admin_priviledges']);

        $this->admin_username = $admin_username;
        $this->admin_password = $admin_password;
        $this->admin_fullname = $admin_fullname;
        $this->admin_email = $admin_email;
        $this->public_key = $public_key;
        $this->email_verification_token = $email_verification_token;
        $this->admin_priviledges = $admin_priviledges;
        $this->usertype = Constants::USERTYPE_ADMIN;

        $this->save();

        $admin_user_id = $this->select('admin_user_id')->where('admin_username', $admin_username)->first()['admin_user_id'];

        $admin = $this->get($admin_user_id, null, null, ["idKey" => "admin_user_id"]);

        return ['data' => $admin['data'], 'error' => $admin['error']];
    }

    public function login($details)
    {
        $admin_username = $details['admin_username'];
        $admin_password = $details['admin_password'];
        $public_key = $details['public_key'];

        if (!(new BaseModel())->isExist($this->where('admin_username', $admin_username)->where('admin_password', $admin_password))) {
            return ['error' => 'Invalid Login credential', 'data' => null];
        }

        self::where('admin_username', $admin_username)->where('admin_password', $admin_password)->update([
            'public_key' => $public_key
        ]);

        $admin = self::select('admin_user_id', 'admin_fullname', 'admin_username', 'admin_password', 'admin_email', 'admin_priviledges', 'email_verified',  'public_key', 'usertype', 'created_at', 'updated_at')->where('admin_username', $admin_username)->where('public_key', $public_key)->where('admin_password', $admin_password)->first();

        return ['data' => $admin, 'error' => ''];
    }

    public function getStruct()
    {
        return self::select('admin_user_id', 'admin_fullname', 'admin_username', 'admin_password', 'admin_email', 'admin_priviledges', 'email_verified',  'usertype', 'created_at', 'updated_at');
    }
}
