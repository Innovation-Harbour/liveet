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
        return $this->hasMany(TimelineMediaModel::class, "timeline_id", "timeline_id");
    }

    public function getStruct()
    {
        return self::select("timeline_id", "event_id", "timeline_desc", "created_at", "updated_at");
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
        $timeline_id = $eventTimelineData["timeline_id"];

        $timeline_media = $details["timeline_media"];

        foreach ($timeline_media as $media) {
            TimelineMediaModel::create(["timeline_id" => $timeline_id, "timeline_media" => $media["path"] ?? $media["url"], "media_type" => $media["type"] ?? ""]);
        }


        return $this->getByPK($timeline_id, null, ["timelineMedia"]);
    }

    public function deleteByPK($pk)
    {
        TimelineMediaModel::where("timeline_id", $pk)->delete();

        return Parent::deleteByPK($pk);
    }
}
