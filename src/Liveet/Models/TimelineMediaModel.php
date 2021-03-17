<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TimelineMediaModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "timeline_media";
    protected $dateFormat = "U";

    public function eventTimeline()
    {
        return $this->belongsTo(EventTimelineModel::class, "timeline_id", "timeline_id");
    }

    public function getStruct()
    {
        $pkKey = $this->primaryKey;
        return self::select($pkKey, "timeline_media_id", "timeline_id", "timeline_media", "media_type", "created_at", "updated_at");
    }
}
