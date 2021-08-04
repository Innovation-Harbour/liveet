<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Controllers\HelperController;
use Liveet\Domain\Constants;

class UserActivityModel extends HelperModel
{
    use SoftDeletes;

    protected $table = "user_activity_log";
    public $incrementing = true;
    protected $dateFormat = "U";
    public $primaryKey = "user_activity_id";
    protected $fillable = ["user_id","activity_type"];

}
