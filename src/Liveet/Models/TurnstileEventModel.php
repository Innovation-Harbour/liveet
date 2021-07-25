<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Controllers\HelperController;
use Liveet\Domain\Constants;

class TurnstileEventModel extends HelperModel
{
    use SoftDeletes;

    protected $table = "turnstile_event";
    public $incrementing = true;
    protected $dateFormat = "U";
    public $primaryKey = "turnstile_event_id";
    protected $fillable = ["turnstile_id,event_ticket_id"];

}
