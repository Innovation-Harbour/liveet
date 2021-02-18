<?php

namespace Rashtell\Models;

use Rashtell\Domain\KeyManager;
use Illuminate\Database\Eloquent\Model;
use Rashtell\Controllers\BaseController;

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

        if (!$this->isExist(static::select('id')->where('id', '>', $start)->limit($limit))) {
            return ['data' => [], 'error' => 'No more data'];
        }

        // $allmodels = $this->getStruct()->where('id', '>', '0')->offset($start)->limit($limit)->get();

        $allmodels = $this->getStruct()->where('id', '>', $start)->limit($limit)->get();

        $total = static::count();

        return ['data' => ["all" => $allmodels, "total" => $total], 'error' => ''];
    }

    public function getByDate($from, $to)
    {
        if (!$this->isExist(static::select('id')->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to))) {
            return ['data' => [], 'error' => 'No more data'];
        }

        $allmodels = $this->getStruct()->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to)->get();

        return ['data' => $allmodels, 'error' => ''];
    }

    public function get($id)
    {
        if (!static::find($id)) {
            return ['data' => [], 'error' => 'Not found'];
        }

        $model = $this->getStruct()->where('id', $id)->first();

        return ['data' => $model, 'error' => ''];
    }

    public function getWithRelationships($id, $relationships)
    {
        if (!static::find($id)) {
            return ['data' => [], 'error' => 'Not found'];
        }

        $model = $this->getStruct()->where('id', $id)->first();

        foreach ($relationships as $relationship) {

            $model[$relationship["table"]];

            foreach ($relationship["unsets"] as $unset) {

                if (isset($model[$relationship["table"]][0])) {

                    foreach ($model[$relationship["table"]] as $val) {
                        unset($val[$unset]);
                    }
                } else {

                    unset($model[$relationship["table"]][$unset]);
                }
            }
        }

        return ['data' => $model, 'error' => ''];
    }

    public function getByColumn($columnName, $columnValue)
    {
        if (!$this->getStruct()->where($columnName, $columnValue)->exists()) {
            return ['data' => [], 'error' => 'Not found'];
        }

        $model = $this->getStruct()->where($columnName, $columnValue)->get();

        return ['data' => $model, 'error' => ''];
    }

    protected function updateByColumn($column, $allInputs)
    {
        $model = $this->where($column, $allInputs[$column]);
        if (!isset($model)) {
            return ['error' => 'Error while updating', 'data' => []];
        }

        $model->update($allInputs);

        // foreach ($allInputs as $columnName => $columnValue) {

        //     $model->$columnName = $columnValue;
        //     $model->save();
        // }

        $model = $this->getByColumn($column, $allInputs[$column]);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    protected function updateColumns($id, $allInputs)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Error while updating', 'data' => []];
        }

        $model->update($allInputs);

        // foreach ($allInputs as $columnName => $columnValue) {

        //     $model->$columnName = $columnValue;
        //     $model->save();
        // }

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function updatePassword($id, $newPassword, $oldPassword)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Password update error: not found', 'data' => []];
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
            return ['error' => 'Password reset error: not found', 'data' => []];
        }

        $model->password = $newPassword;
        $model->public_key = null;
        $model->save();

        $model = $this->get($id);
        $model["data"]["password"] = BaseController::REMA_RESET_PASSWORD;

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function verifyEmail($digest, $status)
    {
        if (!$this->isExist($this->select('id')->where('digest', $digest))) {
            return ['error' => 'Not found, please register again', 'data' => []];
        }

        $id = $this->select('id')->where('digest', $digest)->first()['id'];

        $model = static::find($id);

        if ($model->verified > 0) {
            return ["error" => "Email already verified, please login.", "data" => []];
        }

        $model->verified = $status;
        $model->save();

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function verifyUser($id, $status)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Status update error', 'data' => []];
        }

        if ($model->verified == 0) {
            return ['error' => 'Email not verified'];
        }

        if ($model->verified > 1) {
            return ['error' => "Already verified"];
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
            return ['error' => 'Not found', 'data' => []];
        }

        $model->runSoftDelete();

        return ['data' => ['deleted' => true], 'error' => ''];
    }

    public function logout($id)
    {
        $model = static::find($id);

        if (!$model) {
            return ["error" => "Not found", "data" => []];
        }

        $model->public_key = null;
        $model->save();

        return ["data" => ["logout" => true], "error" => ""];
    }
}
