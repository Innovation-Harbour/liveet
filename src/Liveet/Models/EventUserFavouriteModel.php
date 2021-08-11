<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventUserFavouriteModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_user_favourite";
    protected $dateFormat = "U";
    protected $fillable = ["event_id", "user_id"];
    public $primaryKey = "event_favourite_id";

    public function user()
    {
        return $this->belongsTo(UserModel::class, "user_id", "user_id");
    }

    public function event()
    {
        return $this->belongsTo(EventTicketModel::class, "event_id", "event_id");
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "event_id", "user_id", "created_at", "updated_at");
    }
}
