<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AdminFeatureModel extends BaseModel
{

    protected $table = "admin_feature";
    protected $dateFormat = "U";
    public $primaryKey = "admin_feature_id";
    protected $fillable = ["feature_name", "feature_url"];

    public function adminUsers()
    {
        return $this->belongsToMany(AdminUserModel::class, "admin_feature_user", "admin_feature_id", "admin_user_id", "admin_feature_id", "admin_user_id");
    }

    public function getStruct()
    {
        return self::select("admin_feature_id", "feature_name", "feature_url", "created_at", "updated_at");
    }
}
