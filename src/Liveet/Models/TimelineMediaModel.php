<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TimelineMediaModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "timeline_media";
    protected $dateFormat = "U";
    protected $guarded = [];
    public $primaryKey = "timeline_media_id";
    protected $hidden = ["deleted_at"];

    public function eventTimeline()
    {
        return $this->belongsTo(EventTimelineModel::class, "timeline_id", "timeline_id");
    }

    public function getStruct()
    {
        return self::select("timeline_media_id", "timeline_id", "timeline_media", "media_type", "media_datetime", "created_at", "updated_at");
    }
}
