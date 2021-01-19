<?php

namespace BUS_LOCATOR\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class WalletModel extends BaseModel
{

    protected $table = 'wallets';

    const CREATED_AT = 'dateCreated';
    const UPDATED_AT = 'dateUpdated';
    const DELETED_AT = 'dateDeleted';

    protected $dateFormat = 'U';

    public function user()
    {
        return $this->belongsTo(UserModel::class, "userID");
    }

    public function create($details)
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
        return self::select('id', 'userID', 'locationType', 'previousBalance', 'currentBalance', 'dateCreated', 'dateUpdated');
    }
}
