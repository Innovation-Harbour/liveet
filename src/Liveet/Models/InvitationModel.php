<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Liveet\Models\BaseModel;

class InvitationModel extends BaseModel
{

    protected $table = "event_invitation";
    public $primaryKey = "event_invitation_id";
    protected $guarded = [];

    //public $timestamps = false;

    public function getMobileEvents($user_id, $offset, $limit){

    }
}
