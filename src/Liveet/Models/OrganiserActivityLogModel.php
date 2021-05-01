<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrganiserActivityLogModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "organiser_activity_log";
    protected $dateFormat = "U";
    public $primaryKey = "activity_log_id";

    public function organiserStaff()
    {
        return $this->belongsTo(OrganiserStaffModel::class, "organiser_staff_id", "organiser_staff_id");
    }

    public function getStruct()
    {
        return self::select("activity_log_id", "organiser_staff_id", "activity_log_desc", "created_at", "updated_at");
    }
}
