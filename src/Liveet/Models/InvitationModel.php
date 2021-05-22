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

    //public $timestamps = false;

    public function getMobileEvents($user_id, $offset, $limit){
      $sql = "
              SELECT DISTINCT
              event.event_id,e.event_invitation_status,event.event_venue,event.location_lat,event.location_long,e.invitee_can_invite_count,event.event_multimedia,event.event_name,event.event_date_time,event.event_payment_type,event_control.event_can_invite,event_user_favourite.event_favourite_id
              FROM event_invitation e
              LEFT JOIN user ON e.event_invitee_user_phone = user.user_phone
              RIGHT JOIN event ON e.event_id = event.event_id
              INNER JOIN event_control ON event.event_id = event_control.event_id
              LEFT JOIN event_user_favourite ON event.event_id = event_user_favourite.event_id AND event_user_favourite.user_id = ".$user_id."
              WHERE user.user_id = ".$user_id."
              OR event.event_type = 'PUBLIC' ORDER BY user.user_id DESC, event.event_id LIMIT ".$offset.", ".$limit."
              ";
      $result = $this->getConnection()->select($sql);
      return $result;
    }
}
