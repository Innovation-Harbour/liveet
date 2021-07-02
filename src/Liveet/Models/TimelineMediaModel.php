<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Domain\Constants;

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

    public function createSelf($details, $checks = [])
    {
        $timeline_id = $details["timeline_id"];
        $timeline_media = $details["timeline_media"];

        foreach ($timeline_media as $media) {
            $this->create(["timeline_id" => $timeline_id, "timeline_media" => $media["path"] ?? $media["url"], "media_type" => $media["type"] ?? ""]);
        }


        return (new EventTimelineModel())->getByPK($timeline_id, null, ["timelineMedia"]);
    }

    public function updateByPK($pk, $allInputs, $checks = [])
    {
        $inputError = $this->checkInputError($allInputs, $checks);
        if (null != $inputError) {
            return $inputError;
        }

        unset($allInputs[$this->primaryKey]);

        $query = $this->find($pk);
        if (!$query) {
            return ["error" => Constants::ERROR_NOT_FOUND, "data" => null];
        }

        $timeline_id = $allInputs["timeline_id"];
        $timeline_media = $allInputs["timeline_media"];
        $timeline_media_type = $allInputs["timeline_mediaType"];

        $this->where($this->primaryKey, $pk)->update(["timeline_id" => $timeline_id, "timeline_media" => $timeline_media, "media_type" => $timeline_media_type]);

        $model = $this->getByPK($pk);

        return ["data" => $model["data"], "error" => $model["error"]];
    }

    public function getMobileTimeline($user_id, $offset, $limit){
      $sql = "
              SELECT
              timeline_media,event_multimedia,event_name,media_type, timeline_desc
              FROM  timeline_media
              LEFT JOIN event_timeline ON timeline_media.timeline_id = event_timeline.timeline_id
              LEFT JOIN event on event_timeline.event_id = event.event_id
              LEFT JOIN (SELECT event_id,user_id FROM event_ticket INNER JOIN event_ticket_users ON event_ticket.event_ticket_id = event_ticket_users.event_ticket_id) X ON event.event_id = X.event_id
              WHERE timeline_media.deleted_at IS NULL AND (event.event_type = 'PUBLIC' OR (event.event_type = 'PRIVATE' AND X.user_id = ".$user_id."))
              ORDER BY media_datetime DESC LIMIT ".$offset.", ".$limit."
              ";
      $result = $this->getConnection()->select($sql);
      return $result;
    }
}
