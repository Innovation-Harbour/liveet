<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Controllers\HelperController;
use Liveet\Domain\Constants;

class VerificationLogModel extends HelperModel
{
    use SoftDeletes;

    protected $table = "face_verification_log";
    public $incrementing = true;
    protected $dateFormat = "U";
    public $primaryKey = "verification_log_id";
    protected $fillable = ["event_id","user_id","verification_status","activity_type"];

}
