<?php

namespace Liveet\Models\Mobile;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Models\BaseModel;

class FavouriteModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "event_user_favourite";
    public $primaryKey = "event_favourite_id";
    protected $guarded = [];
    protected $fillable = ['event_id','user_id'];

}
