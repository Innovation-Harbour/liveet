<?php

namespace Liveet\Models\Mobile;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class FavouriteModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_user_favourite";
    public $primaryKey = "event_favourite_id";
    protected $guarded = [];
    protected $fillable = ['event_id','user_id'];

    public function getUserFavourites($user_id, $offset, $limit){
      $sql = "
              SELECT
              event.event_id,event.event_venue,event.location_lat,event.location_long,event_invitation.invitee_can_invite_count,event.event_multimedia,event.event_name,event.event_date_time,event.event_payment_type,event_control.event_can_invite,fav.event_favourite_id
              FROM event_user_favourite fav
              INNER JOIN event ON fav.event_id = event.event_id
              LEFT JOIN user ON fav.user_id = user.user_id
              LEFT JOIN event_control ON fav.event_id = event_control.event_id
              LEFT JOIN event_invitation ON fav.event_id = event_invitation.event_id and user.user_phone = event_invitation.event_invitee_user_phone
              WHERE fav.user_id = ".$user_id."
              ORDER BY event.event_date_time DESC, event.event_id LIMIT ".$offset.", ".$limit."
              ";
      $result = $this->getConnection()->select($sql);
      return $result;
    }

}
