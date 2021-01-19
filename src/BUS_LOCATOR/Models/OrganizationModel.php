<?php

namespace BUS_LOCATOR\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use BUS_LOCATOR\Domain\Constants;
use Rashtell\Domain\CodeLibrary;

class OrganizationModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'organizations';

    const CREATED_AT = 'dateCreated';
    const UPDATED_AT = 'dateUpdated';
    const DELETED_AT = 'dateDeleted';

    protected $dateFormat = 'U';

    public function locations()
    {
        return $this->hasMany(LocationModel::class, "userID");
    }


    public function authenticate($token)
    {
        $authDetails = (new BaseModel())->getTokenInputs($token);

        if ($authDetails == []) {
            return ['isAuthenticated' => false, 'error' => 'Invalid token'];
        }

        $publicKey = $authDetails['publicKey'];

        $user =  self::where('publickey', $publicKey)
            // ->where('token', '=', $token)
            ->first();

        return ($user->exists) ? ['isAuthenticated' => true, 'error' => ''] : ['isAuthenticated' => false, 'error' => 'Expired session'];

        return ['isAuthenticated' => false, 'error' => 'Expired session'];
    }

    public function authenticateWithPublicKey($details)
    {
        $publicKey = $details["publicKey"];

        $user = $this->where(["publicKey" => $publicKey])->exists();
        if (!$user) {
            return ["data" => null, "error" => "Invalid credential"];
        }

        // $user = $this->find(["publicKey" => $publicKey]);
        // if (!$user or sizeof($user) == 0) {
        //     return ["data" => null, "error" => "Invalid credential"];
        // }

        return ["data" => $this->getStruct()->where("publicKey", $publicKey)->first(), "error" => null];
    }

    public function generateNewPublicKey($details)
    {
        $id = $details['id'];
        $cLib = new CodeLibrary();
        $publicKey = $cLib->genID(40, 1);

        $user = $this->find($id);

        if (!$user) {
            return ["data" => null, "error" => "Organization not found"];
        }

        $user->publicKey = $publicKey;
        $user->save();

        return ["data" => ["publicKey" => $publicKey], "error" => null];
    }

    public function getStruct()
    {
        return $this->select('id', 'name', 'phone', 'email', 'userType', 'publicKey', 'dateCreated', 'dateUpdated');
    }

    public function create($details)
    {
        $name = $details['name'];
        $phone = $details['phone'];
        $email = $details['email'];
        $address = $details['address'];

        if ($this->isExist(self::select('id')->where('phone', $phone))) {
            return ['error' => 'Phone number exists', 'data' => null];
        }
        if ($this->isExist(self::select('id')->where('email', $email))) {
            return ['error' => 'Email exists', 'data' => null];
        }

        $this->phone = $phone;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
        $this->userType = Constants::USER_TYPE_ORGANIZATION;

        $this->save();

        $id = $this->select('id')->where('phone', $phone)->where('email', $email)->first()['id'];

        $this->generateNewPublicKey(["id" => $id]);

        $organization = $this->get($id);

        return ['data' => $organization['data'], 'error' => $organization['error']];
    }
}
