<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Event;
use Liveet\APIs\TermiiAPI;
use Liveet\Domain\Constants;
use Rashtell\Domain\CodeLibrary;

class EventAccessModel extends HelperModel
{
    use SoftDeletes;

    protected $table = "event_access";
    protected $dateFormat = "U";
    public $primaryKey = "event_access_id";
    protected $guarded = [];
    protected $hidden = ["deleted_at"];


    public function eventTicket()
    {
        return $this->belongsTo(EventTicketModel::class, "event_ticket_id", "event_ticket_id");
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, "user_id", "user_id");
    }

    public function generateEventAccessCode($input)
    {
        $event_access_code = null;

        $cdl = (new CodeLibrary());
        do {
            $event_access_code = $cdl->genID(6);
        } while ($this->select($this->primaryKey)->where("event_access_code", $event_access_code)->exists());

        return $event_access_code;
    }

    public function createSelf($details, $checks = [""])
    {
        $inputError = $this->checkInputError($details, ["event_ticket_id"], new EventTicketModel());
        if (null != $inputError) {
            return $inputError;
        }

        $event_ticket_id = $details["event_ticket_id"];
        $event_access_population = $details["event_access_population"];

        if ($this->isEventTicketSaleExpired($event_ticket_id)) {
            return ["error" => "Ticket sales closed"];
        }

        $totalTicketCount = (new EventTicketModel())->find($event_ticket_id)->count();
        $usedTicketCount = (new EventTicketUserModel())->where("event_ticket_id", $event_ticket_id)->count();
        // $unusedTicketCount = $totalTicketCount - $usedTicketCount;

        $totalAccessCodeForTicketTypeCount = $this->where("event_ticket_id", $event_ticket_id)->count() + $event_access_population;
        $assignedAccessCodeForTicketTypeCount = $this->where("event_ticket_id", $event_ticket_id)->where("event_access_used_status", Constants::EVENT_ACCESS_ASSIGNED)->count();
        $unAssignedAccessCodeForTicketTypeCount = $this->where("event_ticket_id", $event_ticket_id)->where("event_access_used_status", Constants::EVENT_ACCESS_UNASSIGNED)->count() + $event_access_population;
        $usedAccessCodeForTicketTypeCount = $this->where("event_ticket_id", $event_ticket_id)->where("event_access_used_status", Constants::EVENT_ACCESS_USED)->count();

        $unusedTicketCount = $totalTicketCount - $usedTicketCount - $totalAccessCodeForTicketTypeCount;

        if ($event_access_population > $unusedTicketCount) {
            return ["error" => "Access cards exceeds unused tickets"];
        }

        // var_dump($totalTicketCount, $usedTicketCount, $unusedTicketCount, $createdAccessCodeForTicketTypeCount);

        $event_id = (new EventTicketModel())->find($event_ticket_id)["event_id"];
        $event_code = (new EventModel())->find($event_id)->first()["event_code"];

        for ($i = 0; $i < $event_access_population; $i++) {
            $event_access_code = $event_code . $this->generateEventAccessCode($event_code);

            //create event accesses
            $output = $this->create(["event_ticket_id" => $event_ticket_id, "event_access_code" => $event_access_code]);
        }

        // var_dump($output);
        return ["data" =>
        [
            "total_ticket_count" => $totalTicketCount,
            "used_ticket_count" => $usedTicketCount,
            "total_access_code_for_ticket_type_count" => $totalAccessCodeForTicketTypeCount,
            "unassigned_access_code_for_ticket_type_count" => $unAssignedAccessCodeForTicketTypeCount,
            "assigned_access_code_for_ticket_type_count" => $assignedAccessCodeForTicketTypeCount,
            "used_access_code_for_ticket_type_count" => $usedAccessCodeForTicketTypeCount,
            "unused_ticket_count" => $unusedTicketCount,
        ], "error" => null];
    }

    public function getStruct()
    {
        return self::select("event_access_id", "event_access_code", "event_ticket_id", "user_id", "event_access_used_status", "created_at", "updated_at");
    }

    public function getDashboard($pk, $queryOptions = null, $extras = null)
    {
        $event_id = $extras["event_id"];

        $eventTickets = (new EventTicketModel())->where("event_id", $event_id)->get();

        $eventAccesses = [];
        foreach ($eventTickets as $ticket) {
            $eventAccess["name"] = $ticket["ticket_name"];
            $eventAccess["access_count"] = count($this->where("event_ticket_id", $ticket["event_ticket_id"])->get());

            $eventAccesses[] = $eventAccess;
        }

        if (empty($eventAccesses)) {
            return ["data" => null, "error" => "No access codes found"];
        }

        return ["data" => $eventAccesses, "error" => null];
    }

    public function updateByPK($pk, $allInputs, $checks = [])
    {
        $inputError = $this->checkInputError($allInputs, $checks, (new UserModel()));
        if (null != $inputError) {
            return $inputError;
        }

        unset($allInputs[$this->primaryKey]);

        $eventAccess = $this->find($pk);
        if (!$eventAccess) {
            return ["error" => "Access code not found", "data" => null];
        }

        $user_phone = $allInputs["user_phone"];
        $userQuery = (new UserModel())->select("user_id")->where("user_phone", $user_phone);
        if (!$userQuery->exists()) {
            //send text to phone number
            $event_access_code = $eventAccess["event_access_code"];
            $event = $eventAccess->eventTicket->event;
            $event_name = $event["event_name"];

            $appDownloadLink = Constants::MOBILE_APP_DOWNLOAD_URL;
            $termiiResponse = (new TermiiAPI())->sendSMS($user_phone, "Your access code to $event_name is $event_access_code. Please download the Liveet app at $appDownloadLink to use your access code.");

            // var_dump($termiiResponse);
            //     {
            //         "code":"ok",
            //         "message_id":"5882719683744136910",
            //         "message":"Successfully Sent",
            //         "balance":2942.6,
            //         "user":"Kolapo Obanewa"
            //      }

            if (!isset($termiiResponse->message_id)
                // || (!isset($termiiResponse->code) || $termiiResponse->code != "ok")
            ) {
                return ["data" => null, "error" => "User not registered and sms failed"];
            }

            return ["data" => ["success" => "Access code sent by sms.", "error" => "User not registered"], "error" => null];
        }
        $user_id = $userQuery->first()["user_id"];

        $event_ticket_id = $eventAccess["event_ticket_id"];
        if ($this->isEventTicketSaleExpired($event_ticket_id)) {
            return ["error" => "Ticket sales closed"];
        }

        $eventAccess->update(["user_id" => $user_id, "event_access_used_status" => Constants::EVENT_ACCESS_ASSIGNED]);

        $model = $this->getByPK($pk, null, ["user"]);

        return ["data" => $model["data"], "error" => $model["error"]];
    }

    public function deleteManyByPK($pks)
    {
        $deleteReturn = $this->whereIn($this->primaryKey, $pks)->delete();

        return ["data" => ["deleted" => true, "successCount" => $deleteReturn], "error" => ""];
    }
}
