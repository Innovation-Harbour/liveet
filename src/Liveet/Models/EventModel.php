<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event";
    protected $dateFormat = "U";
    public $primaryKey = "event_id";


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

    public function getEventCode($name)
    {

        $eventCode = null;

        $eventCodeSplit = explode($name, "");
        $eventCodeSplitLenght = count($eventCodeSplit);

        do {
        
        
        } while ($this->select($this->primaryKey)->where("eventCode", $eventCode)->exists());

        return $eventCode;
    }

    public function createSelf($details, $checks = [])
    {
        $organiser_id = $details["organiser_id"];
        $event_name = $details["event_name"];
        $event_desc = $details["event_desc"];
        $event_multimedia = $details["event_multimedia"];
        $event_type = $details["event_type"];
        $event_venue = $details["event_venue"];
        $event_date_time = $details["event_date_time"];
        $event_payment_type = $details["event_payment_type"];
        $event_code = $this->getEventCode($event_name);

        $tickets = $details["tickets"];

        $event_can_invite = $details["event_can_invite"];
        $event_sale_stop_time = $details["event_sale_stop_time"];
        $event_can_transfer_ticket = $details["event_can_transfer_ticket"];
        $event_can_recall = $details["event_can_recall"];

        //create event and get event id
        $eventModelCreate = $this->create(["organiser_id" => $organiser_id, "event_name" => $event_name, "event_code" => $event_code, "event_desc" => $event_desc, "event_multimedia" => $event_multimedia, "event_type" => $event_type, "event_venue" => $event_venue, "event_date_time" => $event_date_time, "event_payment_type" => $event_payment_type]);

        $event_id = $this->select($this->primaryKey)->where("event_code", $event_code)->latest($this->primaryKey)->first()[$this->primaryKey];

        //create all tickets with event id
        $ticketIndex = 0;
        $ticketIDs = [];
        foreach ($tickets as $ticket) {
            $ticket_name = $ticket["ticket_name"];
            $ticket_desc = $ticket["ticket_desc"];
            $ticket_cost = $ticket["ticket_cost"];
            $ticket_population = $ticket["ticket_population"];
            $ticket_discount = $ticket["ticket_discount"];

            $ticketModel = new EventTicketModel();
            $ticketModelCreate = $ticketModel->create(["event_id" => $event_id, "ticket_name" => $ticket_name, "ticket_desc" => $ticket_desc, "ticket_cost" => $ticket_cost, "ticket_population" => $ticket_population, "ticket_discount" => $ticket_discount]);

            $ticketIDs[$ticketIndex] = $ticketModel->select($ticketModel->primaryKey)->where("ticket_name", $ticket_name)->latest($ticketModel->primaryKey)->first()[$ticketModel->primaryKey];

            $ticketIndex++;
        }

        //create event controls
        $eventControlModel = (new EventControlModel());
        $eventControlModelCreate = $eventControlModel->create(["event_id" => $event_id, "event_can_invite" => $event_can_invite, "event_sale_stop_time" => $event_sale_stop_time, "event_can_transfer_ticket" => $event_can_transfer_ticket, "event_can_recall" => $event_can_recall]);
        $eventControls = $eventControlModel->getStruct()->where("event_id", $event_id)->latest($eventControlModel->primaryKey)->first();

        $eventReturn = $this->getByPK($event_id)["data"];
        $eventReturn["tickets"] = $ticketIDs;
        $eventReturn["controls"] = $eventControls;


        var_dump($eventModelCreate, $eventControlModelCreate);

        return ["data" => $eventReturn, "error" => null];
    }

    public function getStruct()
    {
        return self::select("event_id", "event_name", "event_code", "event_desc", "event_multimedia", "event_type", "event_venue", "event_date_time", "organiser_id", "event_payment_type", "created_at", "updated_at");
    }
}
