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

    public function eventTickets()
    {
        return $this->belongsToMany(EventTicketModel::class, "turnstile_event", $this->primaryKey, "event_ticket_id", $this->primaryKey, "event_ticket_id");
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "turnstile_name", "created_at", "updated_at");
    }
}
