<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class VerificationLogModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "face_verification_log";
    public $primaryKey = "verification_log_id";
    protected $guarded = [];
    protected $fillable = ["event_id","user_id","verification_status","activity_type"];

}
