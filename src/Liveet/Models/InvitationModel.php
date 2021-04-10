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
      $sql = "
              SELECT * FROM
              event_invitation e
              LEFT JOIN user ON e.event_invitee_number = user.user_phone
              RIGHT JOIN event ON e.event_id = event.event_id
              LEFT JOIN event_control ON event.event_id = event_control.event_id
              WHERE user.user_id = ".$user_id."
              OR event.event_type = 'PUBLIC' ORDER BY user.user_id DESC LIMIT ".$offset.", ".$limit."
              ";
      $result = $this->getConnection()->select($sql);
      return $result;
    }
}
