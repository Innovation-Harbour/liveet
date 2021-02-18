<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventTicketUserModel extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'event_ticket_users';
    public $incrementing = true;
    protected $dateFormat = 'U';

    public function getStruct()
    {
        return self::select('event_ticket_user_id', 'event_ticket_id', 'user_id', 'user_face_id', 'created_at', 'updated_at');
    }
}
