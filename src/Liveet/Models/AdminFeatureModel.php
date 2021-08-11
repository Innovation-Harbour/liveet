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
        return $this->belongsToMany(AdminUserModel::class, "admin_feature_user", $this->primaryKey, "admin_user_id", $this->primaryKey, "admin_user_id");
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "feature_name", "feature_url", "created_at", "updated_at");
    }
}
