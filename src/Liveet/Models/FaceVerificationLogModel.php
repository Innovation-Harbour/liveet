<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class FaceVerificationLogModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "face_verification_log";
    public $primaryKey = "verification_log_id";
    protected $guarded = [];
    protected $fillable = ["event_id", "user_id", "verification_status", "activity_type"];


    public function user()
    {
        return $this->belongsTo(UserModel::class, "user_id", "user_id");
    }

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "event_id", "user_id", "verification_status", "created_at", "updated_at");
    }
}
