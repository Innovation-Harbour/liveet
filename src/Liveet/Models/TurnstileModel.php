<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class TurnstileModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "turnstile";
    public $primaryKey = "turnstile_id";
    protected $guarded = [];
    protected $fillable = ["turnstile_name"];

}
