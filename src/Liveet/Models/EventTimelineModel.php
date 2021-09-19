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

    public function getMobileTimeline($user_id, $offset, $limit)
    {
        $sql = "
              SELECT
              timeline_id,event_multimedia,event_name,timeline_desc
              FROM  event_timeline
              LEFT JOIN event on event_timeline.event_id = event.event_id
              LEFT JOIN (SELECT event_id,user_id FROM event_ticket INNER JOIN event_ticket_users ON event_ticket.event_ticket_id = event_ticket_users.event_ticket_id WHERE event_ticket_users.ownership_status = 'ACTIVE') X ON event.event_id = X.event_id
              WHERE event_timeline.deleted_at IS NULL AND (event.event_type = 'PUBLIC' OR (event.event_type = 'PRIVATE' AND X.user_id = " . $user_id . "))
              ORDER BY timeline_datetime DESC LIMIT " . $offset . ", " . $limit . "
              ";
        $result = $this->getConnection()->select($sql);
        return $result;
    }
}
