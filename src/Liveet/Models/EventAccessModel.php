<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventAccessModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_access";
    protected $dateFormat = "U";
    public $primaryKey = "event_access_id";

    public function eventTicket()
    {
        return $this->belongsTo(EventTicketModel::class, "event_ticket_id", "event_ticket_id");
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, "user_id", "user_id");
    }

    public function getStruct()
    {
        return self::select("event_access_id", "event_access_code", "event_ticket_id", "event_access_used_status", "created_at", "updated_at");
    }
}
