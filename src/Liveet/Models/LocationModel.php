<?php

namespace Liveet\Models;

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

        $this->createSelf(["organizationID" => $organizationID, "busID" => $busID, "lat" => $lat, "lng" => $lng, "issuerID" => $issuerID, "issuerName" => $issuerName, "time" => $time]);

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

    public function getByDateWithConditions($from, $to, $conditions, $return = null, $options = null)
    {
        // if (!$this->isExist(static::select('id')->where($conditions)->where('time', '>=', $from)->where("time", "<=", $to))) {
        //     return ['data' => null, 'error' => 'No more data'];
        // }

        $query = $return ? $this->select($return) : $this->getStruct();

        if (isset($options["raw"])) {

            $query = $query->selectRaw($options["raw"]);
        }

        $query = $query->where($conditions);

        if ($from != "-") {
            $query = $query->where("time", '>=', $from);
        }

        if ($to != "-") {
            $query = $query->where("time", "<=", $to);
        }

        if (isset($options["distinct"]) and $options["distinct"]) {
            $query = $query->distinct();
        }

        if (isset($options["groupby"])) {
            $query = $query->groupBy($options["groupby"]);
        }

        // var_dump($query->toSql(), $from, $to, $conditions, $return, $options);

        $allmodels = $query->get();

        return ['data' => $allmodels, 'error' => ''];
    }
}
