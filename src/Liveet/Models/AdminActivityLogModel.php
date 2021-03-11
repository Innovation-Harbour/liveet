<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AdminActivityLogModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'admin_activity_log';
    protected $dateFormat = 'U';
    protected $primaryKey = 'activity_log_id';

    public function adminUser()
    {
        return $this->belongsTo(AdminUserModel::class, "admin_user_id", "admin_user_id");
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
        return self::select('activity_log_id', 'admin_user_id', 'acitivity_log_desc', 'created_at', 'updated_at');
    }
}
