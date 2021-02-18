<?php

namespace Rashtel\Models;

use Rashtell\Domain\KeyManager;
use Illuminate\Database\Eloquent\Model;
use Rashtel\Domain\PointsManager;
use Rashtell\Domain\MCrypt;

class BaseModel extends Model
{

    public function authenticate($token)
    {
        $authDetails = (new BaseModel())->getTokenInputs($token);

        if ($authDetails == []) {
            return ['isAuthenticated' => false, 'error' => 'Invalid token'];
        }

        $username = $authDetails['username'];
        $public_key = $authDetails['public_key'];
        $usertype = $authDetails['usertype'];

        $admins =  self::where('public_key', '=', $public_key)
            ->where('username', '=', $username)
            ->where('usertype', '=', $usertype)
            ->take(1)
            ->get();

        foreach ($admins as $admin) {
            // $this->details = $admin;

            return ($admin->exists) ? ['isAuthenticated' => true, 'error' => ''] : ['isAuthenticated' => false, 'error' => 'Expired session'];
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

    protected function isExist($query): bool
    {

        return $query->exists();
    }

    protected static function search($searchTerm)
    {
        return static::select();
    }

    public function getAll($page, $limit)
    {
        $start = ($page - 1) * $limit;

        if (!$this->isExist(static::select('id')->where('id', '>', '0')->offset($start)->limit($limit))) {
            return ['data' => [], 'error' => 'No more data: End'];
        }

        $allmodels = static::getStruct()->where('id', '>', '0')->offset($start)->limit($limit)->get();

        $total = static::count()->where('id', '>', '0')->offset($start)->limit($limit);


        return ['data' => ["all" => $allmodels, "total" => $total], 'error' => ''];
    }

    public function get($id)
    {
        if (!static::find($id)) {
            return ['data' => [], 'error' => 'User does not exist'];
        }

        $model = static::getStruct()->where('id', $id)->first();

        return ['data' => $model, 'error' => ''];
    }

    public function updatePassword($id, $newPassword, $oldPassword)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Error while updating the password', 'data' => []];
        }

        $password  = $model->password;

        if ($oldPassword != $password) {

            return ['error' => 'Incorrect password, please try again', "data" => [],];
        }

        $model->password = $newPassword;
        $model->public_key = null;
        $model->save();

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function resetPassword($id, $newPassword)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Error while updating the password', 'data' => []];
        }

        $model->password = $newPassword;
        $model->public_key = null;
        $model->save();

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function verifyEmail($digest, $status)
    {
        if (!$this->isExist($this->select('id')->where('digest', $digest))) {
            return ['error' => 'User not found, please register again', 'data' => []];
        }

        $id = $this->select('id')->where('digest', $digest)->first()['id'];

        $model = static::find($id);

        if ($model->verified > 0) {
            return ["error" => "This email has already been verified, please login.", "data" => []];
        }

        $model->verified = $status;
        $model->save();

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    protected function updateColumns($id, $allInputs)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Error while updating', 'data' => []];
        }

        foreach ($allInputs as $columnName => $columnValue) {

            $model->$columnName = $columnValue;
            $model->save();
        }

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function verifyUser($id, $status)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Error while updating the status', 'data' => []];
        }

        if ($model->verified == 0) {
            return ['error' => 'Email not verified'];
        }

        if ($model->verified > 1) {
            return ['error' => "User already verified"];
        }

        $model->verified = $status;
        $model->save();

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function deleteById($id)
    {
        $model = static::find($id);

        if (!$model) {
            return ['error' => 'User does not exist', 'data' => []];
        }

        $model->runSoftDelete();

        return ['data' => ['deleted' => true], 'error' => ''];
    }

    public function logout($id)
    {
        $model = static::find($id);

        if (!$model) {
            return ["error" => "User does not exist", "data" => []];
        }

        $model->public_key = null;
        $model->save();

        return ["data" => ["logout" => true], "error" => ""];
    }
}
