<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Domain\Constants;

class EventTimelineModel extends BaseModel
{
    use SoftDeletes;
    use LiveetFunction;

    protected $table = "event_timeline";
    protected $dateFormat = "U";
    protected $guarded = [];
    public $primaryKey = "timeline_id";

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
    }

    public function timelineMedia()
    {
        return $this->hasMany(TimelineMediaModel::class, $this->primaryKey, $this->primaryKey);
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "event_id", "timeline_desc", "timeline_datetime", "created_at", "updated_at");
    }

    public function createSelf($details, $checks = [])
    {
        $event_id = $details["event_id"];
        $timeline_desc = $details["timeline_desc"];

        $eventModel = (new EventModel());
        $event = $eventModel->find($event_id);
        if (!$event) {
            return ["error" => "Event not found", "data" => null];
        }

        $title = Constants::TIMELINE_NOTIFICATION_TITLE;
        $message = Constants::TIMELINE_NOTIFICATION_MESSAGE;
        $eventCode = $event["event_code"];
        $sendNotification = $this->sendMobileNotification(Constants::NOTIFICATION_USER_GROUP, $title, $message, $eventCode);


        $eventTimeline = Parent::createSelf(["event_id" => $event_id, "timeline_desc" => $timeline_desc], $checks);

        if ($eventTimeline["error"]) {
            return $eventTimeline;
        }

        $eventTimelineData = $eventTimeline["data"];
        $timeline_id = $eventTimelineData[$this->primaryKey];

        $timeline_media = $details["timeline_media"];

        foreach ($timeline_media as $media) {
            TimelineMediaModel::create([$this->primaryKey => $timeline_id, "timeline_media" => $media["path"] ?? $media["url"], "media_type" => $media["type"] ?? ""]);
        }


        return $this->getByPK($timeline_id, null, ["timelineMedia"]);
    }

    public function deleteByPK($pk)
    {
        TimelineMediaModel::where($this->primaryKey, $pk)->delete();

        return Parent::deleteByPK($pk);
    }
}
