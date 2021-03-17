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

    public function getStruct()
    {
        return self::select("admin_feature_user_id", "admin_user_id", "admin_feature_id", "created_at", "updated_at");
    }
}
