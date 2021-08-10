<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Controllers\HelperController;
use Liveet\Domain\Constants;

class EventTicketUserModel extends HelperModel
{
    use SoftDeletes;

    protected $table = "event_ticket_users";
    public $incrementing = true;
    protected $dateFormat = "U";
    public $primaryKey = "event_ticket_user_id";
    protected $fillable = ["user_id", "user_face_id"];
    protected $appends = ["ticket_cost"];

    public function eventTicket()
    {
        return $this->belongsTo(EventTicketModel::class, "event_ticket_id", "event_ticket_id");
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, "user_id", "user_id");
    }

    public function getTicketCostAttribute()
    {
        return $this->eventTicket->ticket_cost;
    }

    public function getStruct()
    {
        return self::select("event_ticket_user_id", "event_ticket_id", "user_id", "user_face_id", "created_at", "updated_at");
    }

    public function createSelf($details, $checks = [])
    {
        $eventTicketModel = (new EventTicketModel());

        $event_ticket_id = $details["event_ticket_id"];
        $user_id = $details["user_id"];

        if (!$eventTicketModel->where("event_ticket_id", $event_ticket_id)->exists()) {
            return ["error" => "Invalid ticket"];
        }
        if (!(new UserModel())->where("user_id", $user_id)->exists()) {
            return ["error" => "User not found"];
        }

        if ($this->where("event_ticket_id", $event_ticket_id)->where("user_id", $user_id)->exists()) {
            return ["error" => "User already registered"];
        }

        if ($this->isEventTicketSaleExpired($event_ticket_id)) {
            return ["error" => "Ticket sales closed"];
        }

        return Parent::createSelf($details, []);
    }

    public function getByPage($page, $limit, $return = null, $conditions = null, $relationships = [], $queryOptions = null)
    {
        $whereConditions = [];
        $from = null;
        $to = null;

        if (isset($conditions["from"]) && $conditions["from"]) {
            $from = $conditions["from"];
            unset($conditions["from"]);

            if ($from == "-") {
                $from = date("U") - 86400;
            }

            $whereConditions[] = ["dateCreated", ">=", $from];
        }

        if (isset($conditions["to"]) && $conditions["to"]) {
            $to =  $conditions["to"];
            unset($conditions["to"]);

            if ($to == "-") {
                $to = date("U") + 86400;
            }

            $whereConditions[] = ["dateCreated", "<=", $to];
        }

        if (isset($conditions["event_id"])) {
            $event_id = $conditions["event_id"];
            unset($conditions["event_id"]);
            $event_ticket_ids = (new HelperController())->getEventTicketIds([$event_id]);
            $queryOptions["whereIn"][] = ["event_ticket_id" => $event_ticket_ids];
        }

        foreach ($conditions as $conditionKey => $conditionValue) {
            $whereConditions[] = [$conditionKey, "=", $conditionValue];
        }

        return parent::getByPage($page, $limit, $return, $whereConditions, $relationships, $queryOptions);
    }

    public function updateByPK($pk, $allInputs, $checks = [], $queryOptions = [])
    {
        $inputError = $this->checkInputError($allInputs, $checks);
        if (null != $inputError) {
            return $inputError;
        }

        unset($allInputs[$this->primaryKey]);

        $user_phone = $allInputs["user_phone"];
        $user_face_id = $allInputs["user_face_id"];

        $userQuery = (new UserModel())->select("user_id")->where("user_phone", $user_phone);
        if (!$userQuery->exists()) {
            return ["error" => "User not found", "data" => null];
        }

        $user_id = $userQuery->first()["user_id"];

        $eventTicketUser = $this->find($pk);
        if (!$eventTicketUser) {
            return ["error" => "User ticket not found", "data" => null];
        }

        $event_ticket_id = $eventTicketUser["event_ticket_id"];
        if ($this->isEventTicketSaleExpired($event_ticket_id)) {
            return ["error" => "Ticket sales closed"];
        }
        if (!$this->isEventTicketTransferable($event_ticket_id)) {
            return ["error" => "Ticket is not transferrable"];
        }

        $eventTicketUser->update(["user_id" => $user_id, "user_face_id" => $user_face_id]);

        $model = $this->getByPK($pk, null, ["user", "eventTicket"]);

        return ["data" => $model["data"], "error" => $model["error"]];
    }

    public function recallEventTicket($event_ticket_user_id)
    {
        $query = $this->where("event_ticket_user_id", $event_ticket_user_id);

        if (!$query->exists()) {
            return ["data" => null, "error" => "user ticket not found"];
        }

        $query = $this->getStruct()->where("event_ticket_user_id", $event_ticket_user_id);
        $event_ticket_user = $query->first();

        $event_ticket_user_status = $event_ticket_user["status"];
        if ($event_ticket_user_status == Constants::EVENT_TICKET_USED) {
            return ["error" => "Ticket already used"];
        }
        $event_ticket_user_ownership_status = $event_ticket_user["ownership_status"];
        if ($event_ticket_user_ownership_status != Constants::EVENT_TICKET_ACTIVE) {
            return ["error" => "Invalid Ticket"];
        }

        $event_ticket_id = $event_ticket_user["event_ticket_id"];
        $user_id = $event_ticket_user["user_id"];

        if (!$this->isEventTicketRecallable($event_ticket_id)) {
            return ["error" => "Ticket is not refundable"];
        }

        $user = (new UserModel())->getStruct()->where("user_id", $user_id)->first();
        $query->update(["ownership_status" => Constants::EVENT_TICKET_RECALLED]);
        // $query->delete();
        //TODO: Add refund protocol

        return ["data" => ["type" => "success", "message" => "ticket recalled successfully", "event_ticket_user_id" => $event_ticket_user_id, "user" => $user]];
    }
}
