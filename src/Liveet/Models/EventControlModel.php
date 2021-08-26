<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventControlModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_control";
    protected $dateFormat = "U";
    public $primaryKey = "event_control_id";
    protected $guarded = [];
    protected $hidden = ["deleted_at"];

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "event_id", "event_can_invite", "event_can_transfer_ticket", "event_can_recall", "event_sale_stop_time", "created_at", "updated_at");
    }
}
