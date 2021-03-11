<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AdminFeatureModel extends BaseModel
{

    protected $table = 'admin_feature';
    protected $dateFormat = 'U';
    protected $primaryKey = 'admin_feature_id';

    public function adminUsers()
    {
        return $this->belongsToMany(AdminUserModel::class, "admin_feature_user", "admin_feature_id", "admin_user_id", "admin_feature_id", "admin_user_id");
    }

    public function createSelf($details)
    {
        $userID = $details["userID"];
        $previousBalance = $details["previousBalance"];
        $currentBalance = $details["currentBalance"];
        $locationType = $details["locationType"];

        $this->userID = $userID;
        $this->previousBalance = $previousBalance;
        $this->currentBalance = $currentBalance;
        $this->locationType = $locationType;

        $this->save();

        return ["data" => $this->getStruct()->where("userID", $userID)->latest()->first(), "error" => ""];
    }

    public function getStruct()
    {
        return self::select('admin_feature_id', 'feature_name', 'feature_url', 'created_at', 'updated_at');
    }
}
