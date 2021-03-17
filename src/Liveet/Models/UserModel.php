<?php

namespace Liveet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Liveet\Domain\Constants;
use Rashtell\Domain\CodeLibrary;

class UserModel extends BaseModel
{
    use SoftDeletes;

    protected $table = "user";
    protected $dateFormat = "U";

    public function eventTickets()
    {
        return $this->belongsToMany(EventTicketModel::class, "event_ticket_users", "user_id", "event_ticket_id", "user_id", "event_ticket_id");
    }

    public function payments()
    {
        return $this->hasMany(PaymentModel::class, "user_id", "user_id");
    }


    public function authenticate($token)
    {
        $authDetails = (new BaseModel())->getTokenInputs($token);

        if ($authDetails == []) {
            return ["isAuthenticated" => false, "error" => "Invalid token"];
        }

        $public_key = $authDetails["public_key"];

        $user =  self::where("public_key", $public_key)
            // ->where("token", "=", $token)
            ->first();

        return ($user->exists) ? ["isAuthenticated" => true, "error" => ""] : ["isAuthenticated" => false, "error" => "Expired session"];

        return ["isAuthenticated" => false, "error" => "Expired session"];
    }

    public function authenticateWithPublicKey($details)
    {
        $public_key = $details["public_key"];

        $user = $this->where(["public_key" => $public_key])->exists();
        if (!$user) {
            return ["data" => null, "error" => "Invalid credential"];
        }

        // $user = $this->find(["public_key" => $public_key]);
        // if (!$user or sizeof($user) == 0) {
        //     return ["data" => null, "error" => "Invalid credential"];
        // }

        return ["data" => $this->getStruct()->where("public_key", $public_key)->first(), "error" => null];
    }

    public function generateNewPublicKey($details)
    {
        $pk = $details[$this->primaryKey];
        $cLib = new CodeLibrary();
        $public_key = $cLib->genID(40, 1);

        $user = $this->find($pk);

        if (!$user) {
            return ["data" => null, "error" => "Organization not found"];
        }

        $user->public_key = $public_key;
        $user->save();

        return ["data" => ["public_key" => $public_key], "error" => null];
    }


    public function login($details)
    {
        $username = $details["username"];
        $password = $details["password"];
        $public_key = $details["public_key"];

        if (!(new BaseModel())->isExist($this->where("username", $username)->where("password", $password))) {
            return ["error" => "Invalid Login credential", "data" => null];
        }

        // self::where("username", $username)->where("password", $password)->update([
        //     "public_key" => $public_key
        // ]);

        $pkKey = $this->primaryKey;
        $user = self::select(
            $pkKey,
            "username",
            "name",
            "phone",
            "email",
            "usertype",
            "phoneVerified",
            "emailVerified",
            "public_key",
            "dateCreated",
            "dateUpdated"
        )->where("username", $username)->where("username", $username)->where("password", $password)->first();

        return ["data" => $user, "error" => ""];
    }

    public function getStruct()
    {
        return $this->select("user_id", "user_fullname", "user_phone", "user_email", "user_password", "user_picture", "user_phone_verified", "user_email_verified", "public_key", "created_at", "updated_at");
    }
}
