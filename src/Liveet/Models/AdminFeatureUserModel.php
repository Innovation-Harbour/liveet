<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminFeatureUserModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'admin_feature_user';
    public $incrementing = true;
    protected $dateFormat = 'U';
    protected $primaryKey = 'admin_feature_user_id';

    public function getStruct()
    {
        return self::select('admin_feature_user_id', 'admin_user_id', 'admin_feature_id');
    }
}
