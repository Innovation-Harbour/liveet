<?php

namespace LAMATA_EPURSE\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class BeneficiaryModel extends BaseModel
{

    protected $table = 'beneficiaries';

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    // protected $dateFormat = 'U';


    public function getStruct()
    {
        return self::select('id', 'user_id', 'beneficiary_user_id', 'created_at', 'updated_at');
    }
}
