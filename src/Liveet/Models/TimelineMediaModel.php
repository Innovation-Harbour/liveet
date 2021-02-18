<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TimelineMediaModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'timeline_media';
    protected $dateFormat = 'U';

    public function eventTimeline()
    {
        return $this->belongsTo(EventTimelineModel::class, "timeline_id", "timeline_id");
    }

    public function createSelf($details)
    {
        $userID = $details["userID"];
        $previousBalance = $details["previousBalance"];
        $currentBalance = $details["currentBalance"];
        $locationType = $details["locationType"];

        $this->userID = $userID;
        $this->previousBalance = $previousBalance;
        $this->currentBalance = $currentBalance;
        $this->locationType = $locationType;

        $this->save();

        return ["data" => $this->getStruct()->where("userID", $userID)->latest()->first(), "error" => ""];
    }

    public function getStruct()
    {
        return self::select('id', 'timeline_media_id', 'timeline_id', 'timeline_media', 'media_type', 'created_at', 'updated_at');
    }
}
