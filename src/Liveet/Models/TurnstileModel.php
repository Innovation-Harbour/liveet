<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Controllers\HelperController;
use Liveet\Domain\Constants;

class TurnstileModel extends HelperModel
{
    use SoftDeletes;

    protected $table = "turnstile";
    public $incrementing = true;
    protected $dateFormat = "U";
    public $primaryKey = "turnstile_id";
    protected $fillable = ["turnstile_name"];

}
