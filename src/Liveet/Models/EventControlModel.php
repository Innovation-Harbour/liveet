<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventControlModel extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'event_control';
    protected $dateFormat = 'U';

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
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
        return self::select('event_control_id', 'event_id', 'event_can_invite', 'event_sale_stop_time', 'event_can_transfer_ticket', 'event_can_recall', 'created_at', 'updated_at');
    }
}
