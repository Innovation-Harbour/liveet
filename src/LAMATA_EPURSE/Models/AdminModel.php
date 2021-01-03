<?php

namespace LAMATA_EPURSE\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use LAMATA_EPURSE\Domain\Constants;
use Rashtell\Domain\KeyManager;

class AdminModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'admins';

    const CREATED_AT = 'dateCreated';
    const UPDATED_AT = 'dateUpdated';
    const DELETED_AT = 'dateDeleted';

    protected $dateFormat = 'U';


    public function getDashboard()
    {
        $adminsCount = self::count();
        $organizationsCount = OrganizationModel::count();
        $transactionsCount = TransactionModel::count();

        $dashboard = [
            "adminsCount" => $adminsCount,
            "organizationsCount" => $organizationsCount,
            "transactionsCount" => $transactionsCount,
        ];

        return ["error" => "", "data" => $dashboard];
    }

    public function create($details)
    {
        $username = $details['username'];
        $password = $details['password'];
        $name = $details['name'];
        $phone = $details['phone'];
        $email = $details['email'];
        $address = $details['address'];
        $publicKey = $details['publicKey'];
        $emailVerificationToken = $details['emailVerificationToken'];
        $priviledges = json_encode($details['priviledges']);

        if (!$username or !$password or !$name or !$phone or !$email or !$address or !$priviledges) {
            return ['error' => 'Incomplete input', 'data' => null];
        }

        if (!$username or $this->isExist(self::select('id')->where('username', $username))) {
            return ['error' => 'Username exists', 'data' => null];
        }
        if (!$email or $this->isExist(self::select('id')->where('email', $email))) {
            return ['error' => 'Email exists', 'data' => null];
        }
        if (!$phone or $this->isExist(self::select('id')->where('phone', $phone))) {
            return ['error' => 'Phone number exists', 'data' => null];
        }

        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
        $this->publicKey = $publicKey;
        $this->emailVerificationToken = $emailVerificationToken;
        $this->priviledges = $priviledges;
        $this->userType = Constants::USERTYPE_ADMIN;

        $this->save();

        $id = $this->select('id')->where('username', $username)->first()['id'];

        $admin = $this->get($id);

        return ['data' => $admin['data'], 'error' => $admin['error']];
    }

    public function login($details)
    {
        $username = $details['username'];
        $password = $details['password'];
        $publicKey = $details['publicKey'];

        if (!(new BaseModel())->isExist($this->where('username', $username)->where('password', $password))) {
            return ['error' => 'Invalid Login credential', 'data' => null];
        }

        self::where('username', $username)->where('password', $password)->update([
            'publicKey' => $publicKey
        ]);

        $admin = self::select('id', 'username', 'name', 'phone', 'email', 'address', 'userType', 'phoneVerified', 'emailVerified', 'priviledges', 'publicKey', 'dateCreated', 'dateUpdated')->where('username', $username)->where('publicKey', $publicKey)->where('password', $password)->first();

        return ['data' => $admin, 'error' => ''];
    }

    public function getStruct()
    {
        return self::select('id', 'username', 'name', 'phone', 'email', 'address', 'userType', 'phoneVerified', 'emailVerified', 'priviledges', 'dateCreated', 'dateUpdated');
    }

    public function updateSelf($details)
    {
        $id = $details['id'];
        $username = $details['username'];
        $name = $details['name'];
        $phone = $details['phone'];
        $email = $details['email'];
        $address = $details['address'];

        $admin = $this->find($id);

        if (!$admin) {
            return ['error' => 'This admin user does not exist', 'data' => null];
        }

        if ($this->isExist(self::select('id')->where('email', $email)->where('id', '!=', $id))) {
            return ['error' => 'Email exists', 'data' => null];
        }
        if ($this->isExist(self::select('id')->where('phone', $phone)->where('id', '!=', $id))) {
            return ['error' => 'Phone number exists', 'data' => null];
        }
        if ($this->isExist(self::select('id')->where('username', $username)->where('id', '!=', $id))) {
            return ['error' => 'Username exists', 'data' => null];
        }

        $admin->username = $username;
        $admin->name = $name;
        $admin->phone = $phone;
        $admin->email = $email;
        $admin->address = $address;

        $admin->save();

        $admin = $this->get($id);

        return ['data' => $admin['data'], 'error' => $admin['error']];
    }

    public function updateById($details)
    {
        return $this->updateSelf($details);
    }
}
