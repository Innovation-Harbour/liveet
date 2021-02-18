<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentModel extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'payment';
    protected $dateFormat = 'U';

    public function user()
    {
        return $this->belongsTo(UserModel::class, "user_id", "user_id");
    }

    public function eventTicket()
    {
        return $this->belongsTo(EventTicketModel::class, "event_ticket_id", "event_ticket_id");
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
        return self::select('payment_id', 'event_ticket_id', 'user_id', 'payment_desc', 'payment_amount', 'payment_discount_amount', 'created_at', 'updated_at');
    }
}
