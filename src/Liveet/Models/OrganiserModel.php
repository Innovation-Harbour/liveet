<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Domain\Constants;
use Rashtell\Domain\CodeLibrary;

class OrganiserModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'organiser';
    protected $dateFormat = 'U';


    public function organiserStaff()
    {
        return $this->hasMany(OrganiserStaffModel::class, "organiser_id", "organiser_id");
    }

    public function organiserActivityLogs()
    {
        return $this->hasManyThrough(OrganiserActivityLogModel::class, OrganiserStaffModel::class, "organiser_id", "organiser_staff_id", "organiser_id", "organiser_staff_id");
    }

    public function events()
    {
        return $this->hasMany(EventModel::class, "organiser_id", "organiser_id");
    }

    public function createSelf($details)
    {
        $username = $details['username'];
        $password = $details['password'];
        $name = $details['name'];
        $phone = $details['phone'];
        $email = $details['email'];
        $address = $details['address'] ?? "";

        if ($this->isExist(self::select('id')->where('username', $username))) {
            return ['error' => 'Username exists', 'data' => null];
        }
        if ($this->isExist(self::select('id')->where('phone', $phone))) {
            return ['error' => 'Phone number exists', 'data' => null];
        }
        if ($this->isExist(self::select('id')->where('email', $email))) {
            return ['error' => 'Email exists', 'data' => null];
        }

        $this->username = $username;
        $this->password = $password;
        $this->phone = $phone;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
        $this->usertype = Constants::USER_TYPE_ORGANIZATION;

        $this->save();

        $id = $this->select('id', 'username', 'name', 'phone', 'email', 'usertype', 'public_key', 'dateCreated', 'dateUpdated')->where('username', $username)->where('phone', $phone)->first()['id'];

        // $this->generateNewPublicKey(["id" => $id]);

        $organization = $this->get($id);

        return ['data' => $organization['data'], 'error' => $organization['error']];
    }

    public function getStruct()
    {
        return $this->select('organiser_id', 'organiser_name', 'organiser_email', 'organiser_phone', 'phone_verified', 'email_verified', 'created_at', 'updated_at');
    }
}
