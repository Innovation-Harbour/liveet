<?php

namespace Liveet\Models\Mobile;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class TempModel extends BaseModel
{
  use SoftDeletes;

    protected $table = "temp_user";
    public $primaryKey = "temp_id";
    protected $guarded = [];

    public $timestamps = false;
}
