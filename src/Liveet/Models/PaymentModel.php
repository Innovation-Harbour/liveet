<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "payment";
    protected $dateFormat = "U";

    public function user()
    {
        return $this->belongsTo(UserModel::class, "user_id", "user_id");
    }

    public function eventTicket()
    {
        return $this->belongsTo(EventTicketModel::class, "event_ticket_id", "event_ticket_id");
    }

    public function getStruct()
    {
        return self::select("payment_id", "event_ticket_id", "user_id", "payment_desc", "created_at", "updated_at");
    }

    public function createSelf($details, $checks = [])
    {
        return Parent::createSelf($details, []);
    }
}
