<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AdminActivityLogModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "admin_activity_log";
    protected $dateFormat = "U";
    public $primaryKey = "activity_log_id";

    public function adminUser()
    {
        return $this->belongsTo(AdminUserModel::class, "admin_user_id", "admin_user_id");
    }

    public function getStruct()
    {
        return self::select("activity_log_id", "admin_user_id", "acitivity_log_desc", "created_at", "updated_at");
    }
}
