<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventModel extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'event';
    protected $dateFormat = 'U';
    protected $primaryKey = 'event_id';


    public function organiser()
    {
        return $this->belongsTo(OrganiserModel::class, "organiser_id", "organiser_id");
    }

    public function eventControl()
    {
        return $this->hasOne(EventModel::class, "event_id", "event_id");
    }

    public function eventTickets()
    {
        return $this->hasMany(EventTicketModel::class, "event_id", "event_id");
    }

    public function eventAccesses()
    {
        return $this->hasManyThrough(EventAccessModel::class, EventTicketModel::class, "event_id", "event_ticket_id", "event_id", "event_ticket_id");
    }

    public function eventTimelines()
    {
        return $this->hasMany(EventTimelineModel::class, "event_id", "event_id");
    }

    public function eventTimelineMedias()
    {
        return $this->hasManyThrough(TimelineMediaModel::class, EventTimelineModel::class, "event_id", "timeline_id", "event_id", "timeline_id");
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
        return self::select('event_id', 'event_name', 'event_code', 'event_desc', 'event_multimedia', 'event_type', 'event_type', 'event_venue', 'event_date_time', 'organiser_id', 'event_payment_type', 'created_at', 'updated_at');
    }
}
