<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EventTimelineModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_timeline";
    protected $dateFormat = "U";

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
    }

    public function timelineMedias()
    {
        return $this->hasMany(TimelineMediaModel::class, "timeline_id", "timeline_id");
    }

    public function getStruct()
    {
        return self::select("timeline_id", "event_id", "tiemline_desc", "created_at", "updated_at");
    }
}
