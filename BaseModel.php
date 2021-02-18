<?php

namespace Liveet\Models;

use Rashtell\Domain\KeyManager;
use Illuminate\Database\Eloquent\Model;
use Liveet\Domain\Constants;

class BaseModel extends Model
{

    public function authenticate($token)
    {
        $authDetails = (new BaseModel())->getTokenInputs($token);

        if ($authDetails == []) {
            return ['isAuthenticated' => false, 'error' => 'Invalid token'];
        }

        $public_key = $authDetails['public_key'];
        $username = $authDetails['username'];
        $userType = $authDetails['userType'];

        $users =  self::where('public_key', $public_key)
            ->where('username', '=', $username)
            ->where('userType', '=', $userType)
            ->take(1)
            ->get();

        foreach ($users as $user) {
            return ($user->exists) ? ['isAuthenticated' => true, 'error' => ''] : ['isAuthenticated' => false, 'error' => 'Expired session'];
        }

        return ['isAuthenticated' => false, 'error' => 'Expired session'];
    }

    public function getTokenInputs($token)
    {
        $kmg = new KeyManager();
        $auth = $kmg->validateClaim($token);

        if (!$auth) {
            return [];
        }

        return (array) $auth;
    }

    public function checkInputError($details, $required)
    {
        $checkExistsError =  $this->checkExistsError($details, $required);
        if (null != $checkExistsError) {
            return $checkExistsError;
        }

        $checkFormatError = $this->checkFormatError($details, $required);
        if (null != $checkFormatError) {
            return $checkFormatError;
        }
    }

    public function checkExistsError($details, $required)
    {
        $existExceptions =  ["password"];
        $isCreate = true;
        if (isset($details["id"])) {
            $model = $this->find($details["id"]);
            if (!$model) {
                return ['error' => Constants::ERROR_NOT_FOUND];
            }

            $isCreate = false;
        }

        $returnVal = null;
        foreach ($required as $key) {
            if (!isset($details[$key])) {
                $returnVal = ["error" => $key . " is required"];
                break;
            }

            if (!$details[$key]) {
                $returnVal = ["error" => $details[$key] . " is invalid"];
                break;
            }

            if (in_array($key, $existExceptions)) {
                continue;
            }

            if ($isCreate) {
                if ($this->isExist($this->select('id')->where($key, $details[$key]))) {
                    $returnVal = ['error' => $key . ' exists'];
                    break;
                }
            } else {
                if ($this->isExist($this::select('id')->where($key, $details[$key])->where('id', '!=', $details["id"]))) {
                    $returnVal = ['error' => $key . ' exists'];
                    break;
                }
            }
        }

        return $returnVal;
    }

    public function checkFormatError($details, $required)
    {
        foreach ($required as $key) {
            if (!isset($details[$key])) {
                return ["error" => $key . " is required"];
            }

            $arrsUnallowed = array("admin", "administrator", "username", "social", "intagram", "facebook", "twitter", "error");

            if ($key == "username" && (!$details["username"] || preg_match('/[^a-z_\-0-9]/i', $details["username"]) || in_array($details["username"], $arrsUnallowed))) {

                return ['error' => 'Invalid username'];
            }
        }
    }

}
