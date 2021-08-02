<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Liveet\Models\BaseModel;

class InvitationModel extends BaseModel
{

    protected $table = "event_invitation";
    public $primaryKey = "event_invitation_id";
    protected $guarded = [];
    protected $fillable = ['event_id','invitation_name','event_inviter_user_id','event_invitee_user_phone','invitee_can_invite_count','event_invitation_status'];

}
