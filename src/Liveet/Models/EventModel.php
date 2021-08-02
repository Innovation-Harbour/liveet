<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Event;
use Rashtell\Domain\CodeLibrary;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Domain\Constants;

class EventModel extends BaseModel
{
    use SoftDeletes;
    use LiveetFunction;

    protected $table = "event";
    protected $dateFormat = "U";
    public $primaryKey = "event_id";
    protected $guarded = [];
    protected $with = ["eventTickets", "eventControl"];
    protected $hidden = ["deleted_at"];

    public function organiser()
    {
        return $this->belongsTo(OrganiserModel::class, "organiser_id", "organiser_id");
    }

    public function eventControl()
    {
        return $this->hasOne(EventControlModel::class, "event_id", "event_id");
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

    public function generateEventCode($name)
    {

        $event_code = null;

        $eventCodeSplit = explode($name, "");
        $eventCodeSplitLenght = count($eventCodeSplit);

        $cdl = (new CodeLibrary());
        do {
            $event_code = $cdl->genID(6);
        } while ($this->select($this->primaryKey)->where("event_code", $event_code)->exists());

        return $event_code;
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
        $event_code = $this->generateEventCode($event_name);

        $tickets = $details["tickets"];

        $event_can_invite = $details["event_can_invite"];
        $event_sale_stop_time = $details["event_sale_stop_time"];
        $event_can_transfer_ticket = $details["event_can_transfer_ticket"];
        $event_can_recall = $details["event_can_recall"];

        $status = ""; // "done" on success, "error" on failure
        $i = 0;
        do {
            $status = $this->createAwsEvent($event_code);
            $i++;
        } while ($status == "error" && $i < Constants::CREATE_AWS_EVENT_RETRY);
        if ($status == "error") {
            return ["data" => null, "error" => "An error occured while creating aws event"];
        }

        [$address_found,$latitude,$longitude] = $this->getCoordinates($event_venue);
        //["address_found" => $address_found, "longitude" => $longitude, "latitude" => $latitude] = $cordinates;

        //create event
        $this->create(["organiser_id" => $organiser_id, "event_name" => $event_name, "event_code" => $event_code, "event_desc" => $event_desc, "event_multimedia" => $event_multimedia, "event_type" => $event_type, "event_venue" => $event_venue, "event_date_time" => $event_date_time, "event_payment_type" => $event_payment_type
        , "location_lat" => $latitude, "location_long" => $longitude]);

        //Get event id
        $event_id = $this->select($this->primaryKey)->where("event_code", $event_code)->latest($this->primaryKey)->first()[$this->primaryKey];

        //create all tickets with event id
        $ticketIndex = 0;
        $ticketIDs = [];
        foreach ($tickets as $ticket) {
            $ticketModel = new EventTicketModel();
            $ticket_cost = $event_payment_type == Constants::PAYMENT_TYPE_FREE ? 0 : $ticket->ticket_cost;

            $ticketModel->create(["event_id" => $event_id, "ticket_name" => $ticket->ticket_name, "ticket_desc" => $ticket->ticket_desc, "ticket_cost" => $ticket_cost, "ticket_population" => $ticket->ticket_population, "ticket_discount" => $ticket->ticket_discount]);

            $ticketIDs[$ticketIndex] = $ticketModel->select($ticketModel->primaryKey)->where("event_id", $event_id)->latest($ticketModel->primaryKey)->first()[$ticketModel->primaryKey];

            $ticketIndex++;
        }

        //create event controls
        $eventControlModel = (new EventControlModel());
        $eventControlModel->create(["event_id" => $event_id, "event_can_invite" => $event_can_invite, "event_sale_stop_time" => $event_sale_stop_time, "event_can_transfer_ticket" => $event_can_transfer_ticket, "event_can_recall" => $event_can_recall]);

        $eventReturn = $this->getByPK($event_id)["data"];
        // $eventControls = $eventControlModel->getStruct()->where("event_id", $event_id)->latest($eventControlModel->primaryKey)->first();
        // $eventReturn["event_tickets"] = (new EventTicketModel())->where("event_id", $event_id)->get();
        // +$eventReturn["event_controls"] = $eventControls;

        if (!$address_found) {
            $eventReturn["error"] = "The address wasn’t found. Please kindly note that the map services for directing users to the event location won’t work.";
        }

        return ["data" => $eventReturn, "error" => null];
    }

    public function getStruct()
    {
        return self::select("event_id", "event_name", "event_code", "event_desc", "event_multimedia", "event_type", "event_venue", "event_date_time", "location_lat", "location_long", "organiser_id", "event_payment_type", "created_at", "updated_at");
    }

    public function updateByPk($pk, $details, $checks = [])
    {
        $event_name = $details["event_name"];
        $event_desc = $details["event_desc"];
        $event_multimedia = $details["event_multimedia"];
        $event_type = $details["event_type"];
        $event_venue = $details["event_venue"];
        $event_date_time = $details["event_date_time"];
        $event_payment_type = $details["event_payment_type"];

        $tickets = $details["tickets"];

        $event_can_invite = $details["event_can_invite"];
        $event_sale_stop_time = $details["event_sale_stop_time"];
        $event_can_transfer_ticket = $details["event_can_transfer_ticket"];
        $event_can_recall = $details["event_can_recall"];

        $cordinates = $this->getCoordinates($event_venue);
        ["address_found" => $address_found, "longitude" => $longitude, "latitude" => $latitude] = $cordinates;

        //create event
        $this->find($pk)->update(["event_name" => $event_name, "event_desc" => $event_desc, "event_multimedia" => $event_multimedia, "event_type" => $event_type, "event_venue" => $event_venue, "event_date_time" => $event_date_time, "event_payment_type" => $event_payment_type, "location_lat" => $latitude, "location_long" => $longitude]);

        //Get event id
        $event_id = $pk;

        //create all tickets with event id
        $ticketIndex = 0;
        $ticketIDs = [];
        $ticketModel = new EventTicketModel();

        foreach ($tickets as $ticket) {
            $event_ticket_id = $ticket->event_ticket_id;
            $ticketQuery = $ticketModel->where("event_ticket_id", $event_ticket_id)->where("event_id", $event_id);

            if ($ticketQuery->exists()) {
                $ticket_cost = $event_payment_type == Constants::PAYMENT_TYPE_FREE ? 0 : $ticket->ticket_cost;

                $ticketQuery->update(["ticket_name" => $ticket->ticket_name, "ticket_desc" => $ticket->ticket_desc, "ticket_cost" => $ticket_cost, "ticket_population" => $ticket->ticket_population, "ticket_discount" => $ticket->ticket_discount]);
            } else {
                $ticketInstance = new EventTicketModel();

                $ticketInstance->create(["event_id" => $event_id, "ticket_name" => $ticket->ticket_name, "ticket_desc" => $ticket->ticket_desc, "ticket_cost" => $ticket->ticket_cost, "ticket_population" => $ticket->ticket_population, "ticket_discount" => $ticket->ticket_discount]);

                $event_ticket_id = $ticketInstance->select($ticketInstance->primaryKey)->where("event_id", $event_id)->latest($ticketModel->primaryKey)->first()[$ticketModel->primaryKey];
            }

            $ticketIDs[$ticketIndex] = $event_ticket_id;

            $ticketIndex++;
        }

        //create event controls
        $eventControlModel = (new EventControlModel());
        $eventControlQuery = $eventControlModel->where("event_id", $event_id);
        if ($eventControlQuery->exists()) {
            $eventControlQuery->update(["event_can_invite" => $event_can_invite, "event_sale_stop_time" => $event_sale_stop_time, "event_can_transfer_ticket" => $event_can_transfer_ticket, "event_can_recall" => $event_can_recall]);
        } else {

            $eventControlQuery->create(["event_id" => $event_id, "event_can_invite" => $event_can_invite, "event_sale_stop_time" => $event_sale_stop_time, "event_can_transfer_ticket" => $event_can_transfer_ticket, "event_can_recall" => $event_can_recall]);
        }

        $eventReturn = $this->find($event_id);
        // $eventControls = $eventControlModel->getStruct()->where("event_id", $event_id)->latest($eventControlModel->primaryKey)->first();
        // $eventReturn["event_tickets"] = (new EventTicketModel())->where("event_id", $event_id)->get();
        // $eventReturn["event_controls"] = $eventControls;

        if (!$address_found) {
            $eventReturn["error"] = "The address wasn’t found. Please kindly note that the map services for directing users to the event location won’t work.";
        }

        return ["data" => $eventReturn, "error" => null];
    }

    public function getMobileEvents($user_id, $offset, $limit){
      $sql = "
                SELECT * FROM event
                LEFT JOIN
                  (SELECT event_id AS invitation_event_id,invitee_can_invite_count,event_invitation_status,user_id FROM event_invitation INNER JOIN user ON event_invitation.event_invitee_user_phone = user.user_phone WHERE user.user_id = ".$user_id.") X ON event.event_id = X.invitation_event_id
                WHERE event.event_type = 'PUBLIC' OR (event.event_type = 'PRIVATE' AND user_id = ".$user_id.")
                ORDER BY event.event_date_time DESC, event.event_id DESC
                LIMIT ".$offset.", ".$limit."
              ";
      $result = $this->getConnection()->select($sql);
      return $result;
    }
}
