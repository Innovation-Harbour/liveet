<?php

namespace BUS_LOCATOR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'locations';

    const CREATED_AT = 'dateCreated';
    const UPDATED_AT = 'dateUpdated';
    const DELETED_AT = 'dateDeleted';

    protected $dateFormat = 'U';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(OrganizationModel::class, "organizationID");
    }

    public function createMany($details)
    {
        $organizationID = $details["organizationID"];
        $busID = $details["busID"];
        $lat = $details["lat"];
        $lng = $details["lng"];
        $issuerID = $details["issuerID"];
        $issuerName = $details["issuerName"];
        $time = $details["time"];

        $user = OrganizationModel::find($organizationID);
        if (!$user) {
            return ["data" => null, "error" => ["type" => "error", "Invalid request"]];
        }

        $this->create(["organizationID" => $organizationID, "busID" => $busID, "lat" => $lat, "lng" => $lng, "issuerID" => $issuerID, "issuerName" => $issuerName, "time" => $time]);

        // $this->organizationID = $organizationID;
        // $this->busID = $busID;
        // $this->lat = $lat;
        // $this->lng = $lng;
        // $this->issuerID = $issuerID;
        // $this->issuerName = $issuerName;
        // $this->time = $time;
        // $this->save();

        return ['data' => [$issuerID . "_" . $busID . "_" . $time => true], 'error' => ""];
    }


    public function getStruct()
    {
        return self::select('id', 'busID', 'lat', 'lng', 'issuerID', 'issuerName', 'time', 'dateCreated', 'dateUpdated');
    }

    public function getByDateWithConditions($from, $to, $conditions, $return = null)
    {
$conditions["organizationID"] = 5;
        if (!$this->isExist(static::select('id')->where($conditions)->where('time', '>=', $from)->where("time", "<=", $to))) {
            return ['data' => null, 'error' => 'No more data'];
        }

        $allmodels = $return ? $this->select($return)->where($conditions)->where('time', '>=', $from)->where("time", "<=", $to)->get() : $this->getStruct()->where($conditions)->where('time', '>=', $from)->where("time", "<=", $to)->get();

        return ['data' => $allmodels, 'error' => ''];
    }
}
