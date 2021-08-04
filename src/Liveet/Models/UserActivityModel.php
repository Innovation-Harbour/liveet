<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class UserActivityModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "user_activity_log";
    public $primaryKey = "user_activity_id";
    protected $guarded = [];
    protected $fillable = ["user_id","activity_type"];

}
