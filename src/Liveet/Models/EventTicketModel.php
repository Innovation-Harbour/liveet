<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventTicketModel extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'event_ticket';
    protected $dateFormat = 'U';

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
    }

    public function eventAccess()
    {
        return $this->hasOne(EventAccessModel::class, "event_ticket_id", "event_ticket_id");
    }

    public function users()
    {
        return $this->belongsToMany(EventModel::class, "event_ticket_users", "event_ticket_id", "user_id", "event_ticket_id", "user_id");
    }

    public function payments()
    {
        return $this->hasMany(PaymentModel::class, "event_ticket_id", "event_ticket_id");
    }

    public function createSelf($details)
    {
        $userID = $details["userID"];
        $previousBalance = $details["previousBalance"];
        $currentBalance = $details["currentBalance"];
        $locationType = $details["locationType"];

        $this->userID = $userID;
        $this->previousBalance = $previousBalance;
        $this->currentBalance = $currentBalance;
        $this->locationType = $locationType;

        $this->save();

        return ["data" => $this->getStruct()->where("userID", $userID)->latest()->first(), "error" => ""];
    }

    public function getStruct()
    {
        return self::select('event_ticket_id', 'event_id', 'ticket_name', 'ticket_desc', 'ticket_cost', 'ticket_population', 'ticket_discount', 'created_at', 'updated_at');
    }
}
