<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventTicketModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_ticket";
    protected $dateFormat = "U";
    public $primaryKey = "event_ticket_id";
    protected $guarded = [];
    protected $hidden = ["deleted_at"];

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
    }

    public function eventAccess()
    {
        return $this->hasOne(EventAccessModel::class, $this->primaryKey, $this->primaryKey);
    }

    public function users()
    {
        return $this->belongsToMany(EventModel::class, "event_ticket_users", $this->primaryKey, "user_id", $this->primaryKey, "user_id");
    }

    public function payments()
    {
        return $this->hasMany(PaymentModel::class, $this->primaryKey, $this->primaryKey);
    }

    public function eventTicketUsers()
    {
        return $this->hasMany(EventTicketUserModel::class, "event_ticket_user_id", "event_ticket_user_id");
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "event_id", "ticket_name", "ticket_desc", "ticket_cost", "ticket_population", "ticket_discount", "created_at", "updated_at");
    }
}
