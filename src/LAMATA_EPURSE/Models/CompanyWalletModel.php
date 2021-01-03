<?php

namespace LAMATA_EPURSE\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyWalletModel extends BaseModel
{

    protected $table = 'company_wallets';

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    // protected $dateFormat = 'U';


    public function getStruct()
    {
        return self::select('id', 'previous_balance', 'current_balance', 'created_at', 'updated_at');
    }
}
