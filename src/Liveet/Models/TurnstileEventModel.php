<?php

namespace Liveet\Models;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class TurnstileEventModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "turnstile_event";
    protected $guarded = [];
    public $primaryKey = "turnstile_event_id";
    protected $fillable = ["turnstile_id,event_ticket_id"];


    public function getStruct(){
        return $this->select($this->primaryKey, "turnstile_id", "event_ticket_it", "created_at", "updated_at");
    }
}
