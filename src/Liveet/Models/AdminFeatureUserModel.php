<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminFeatureUserModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "admin_feature_user";
    // public $incrementing = true;
    protected $dateFormat = "U";
    public $primaryKey = "admin_feature_user_id";
    protected $fillable = ["admin_user_id", "admin_feature_id"];

    public function adminUsers()
    {
        return $this->belongsTo(AdminUserModel::class, "admin_user_id", $this->primaryKey);
    }

    public function adminFeatures()
    {
        return $this->belongsTo(AdminFeatureModel::class, "admin_feature_id", $this->primaryKey);
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "admin_user_id", "admin_feature_id", "created_at", "updated_at");
    }
}
