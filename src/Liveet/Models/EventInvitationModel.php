<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\APIs\TermiiAPI;
use Liveet\Domain\Constants;

class EventInvitationModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_invitation";
    protected $dateFormat = "U";
    public $primaryKey = "event_invitation_id";
    protected $guarded = [];
    protected $hidden = ["deleted_at"];

    public function event()
    {
        return $this->belongsTo(EventModel::class, "event_id", "event_id");
    }

    public function inviter()
    {
        return $this->belongsTo(UserModel::class, "event_inviter_user_id", "user_id");
    }

    public function invitee()
    {
        return $this->belongsTo(UserModel::class, "event_invitee_user_phone", "user_phone");
    }

    public function getStruct()
    {
        return $this->select($this->primaryKey, "event_id", "invitation_name", "event_inviter_user_id", "event_invitee_user_phone", "invitee_can_invite_count", "event_invitation_status", "created_at", "updated_at");
    }

    public function createSelf($details, $checks = [])
    {
        $event_id = $details["event_id"];
        $event_inviter_user_id = $details["event_inviter_user_id"];
        $event_invitee_user_phone = $details["event_invitee_user_phone"];

        $event = EventModel::find($event_id);
        if (!$event_id) {
            return ["data" => null, "error" => "Event not found"];
        }

        if ($event["event_type"] == Constants::EVENT_TYPE_PUBLIC) {
            $details["invitee_can_invite_count"] = 0;
        }

        if ($event_inviter_user_id) {
            $inviter = UserModel::find($event_inviter_user_id);
            if (!$inviter) {
                return ["data" => null, "error" => "Inviter not found"];
            }
        }

        $inviteeQuery = UserModel::where("user_phone", $event_invitee_user_phone);
        if (!$inviteeQuery->exists()) {
            $appDownloadLink = Constants::MOBILE_APP_DOWNLOAD_URL;
            $eventName = $event->event_name;

            $termiiResponse = (new TermiiAPI())->sendSMS($event_invitee_user_phone, "An invitation has been sent to you for $eventName event. Kindly download the Liveet app at $appDownloadLink to accept the invitation.");

            // return ["data" => null, "error" => "Invitee not found"];
        } else {
            $invitee = $inviteeQuery->first();

            if ($invitee["user_id"] == $inviter["user_id"]) {
                return ["data" => null, "error" => "You cannot invite yourself"];
            }
        }

        if ($this->where(["event_id" => $event_id, "event_inviter_user_id" => $event_inviter_user_id, "event_invitee_user_phone" => $event_invitee_user_phone])->exists()) {
            return ["data" => null, "error" => "Duplicate invitation"];
        }

        return Parent::createSelf($details, $checks);
    }
}
