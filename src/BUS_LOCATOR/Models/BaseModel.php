<?php

namespace BUS_LOCATOR\Models;

use Rashtell\Domain\KeyManager;
use Illuminate\Database\Eloquent\Model;
use BUS_LOCATOR\Controllers\BaseController;

class BaseModel extends Model
{

    public function authenticate($token)
    {
        $authDetails = (new BaseModel())->getTokenInputs($token);

        if ($authDetails == []) {
            return ['isAuthenticated' => false, 'error' => 'Invalid token'];
        }

        $publicKey = $authDetails['publicKey'];
        $username = $authDetails['username'];
        $userType = $authDetails['userType'];

        $users =  self::where('publickey', $publicKey)
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

    protected function isExist($query): bool
    {

        return $query->exists();
    }

    protected static function search($searchTerm)
    {
        return static::select();
    }

    public function getAll($page, $limit, $return = null, $conditions = null, $options = null)
    {
        $minID = $this->min("id");

        $start = $minID + (($page - 1) * $limit) - 1;

        if (!$this->isExist(static::select('id')->where('id', '>', $start))) {
            return ['data' => null, 'error' => 'No more data'];
        }

        // $allmodels = $this->getStruct()->where('id', '>', '0')->offset($start)->limit($limit)->get();

        $query = $return ?
            $this->select($return)->where('id', '>', $start) :
            $this->getStruct()->where('id', '>', $start);

        if ($conditions) {
            $query = $query->where($conditions);
        }

        if ($options and isset($options["distinct"]) and $options["distinct"]) {
            $query = $query->distinct();
        }

        $query = $query->limit($limit);

        $allmodels = $query->get();
        $total = $query->count();

        return ['data' => ["all" => $allmodels, "total" => $total], 'error' => ''];
    }

    public function getByDate($from, $to, $return = null)
    {
        if (!$this->isExist(static::select('id')->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to))) {
            return ['data' => null, 'error' => 'No more data'];
        }

        $allmodels = $return ? $this->select($return)->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to)->get() : $this->getStruct()->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to)->get();

        return ['data' => $allmodels, 'error' => ''];
    }

    public function getByDateWithRelationship($from, $to, $relationships, $return = null)
    {
        if (!$this->isExist(static::select('id')->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to))) {
            return ['data' => null, 'error' => 'No more data'];
        }

        $allmodels = $return ? $this->select($return)->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to)->get() : $this->getStruct()->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to)->get();

        foreach ($allmodels as $models) {

            foreach ($relationships as $relationship) {

                $models[$relationship["table"]];

                foreach ($relationship["unsets"] as $unset) {
                    // var_dump($relationship["table"]);

                    if (isset($models[$relationship["table"]][0])) {

                        foreach ($models[$relationship["table"]] as $val) {

                            unset($val[$unset]);
                        }
                    } else {

                        if (isset($models[$relationship["table"]][$unset])) {

                            unset($models[$relationship["table"]][$unset]);
                        }
                    }
                }
            }
        }

        return ['data' => $allmodels, 'error' => ''];
    }

    public function getByDateWithConditions($from, $to, $conditions, $return = null, $options = null)
    {
        if (!$this->isExist(static::select('id')->where($conditions)->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to))) {
            return ['data' => null, 'error' => 'No more data'];
        }

        $query = $return ? $this->select($return)->where($conditions)->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to) : $this->getStruct()->where($conditions)->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to);

        if (isset($options["distinct"]) and $options["distinct"]) {
            $query = $query->distinct();
        }

        if (isset($options["max"])) {
            $max = [""];
        }

        $allmodels = $query->get();

        return ['data' => $allmodels, 'error' => ''];
    }

    public function get($id, $return = null, $options = ["distinct" => false])
    {
        if (!$this::find($id)) {
            return ['data' => null, 'error' => 'Does not exist'];
        }

        $model = ($return ? $this->select($return)->where('id', $id)->first() : $this->getStruct()->where('id', $id)->first());

        return ['data' => $model, 'error' => ''];
    }

    public function getWithRelationships($id, $relationships, $return = null)
    {
        if (!static::find($id)) {
            return ['data' => null, 'error' => 'Does not exist'];
        }

        $model = $return ? $this->select($return)->where('id', $id)->first() : $this->getStruct()->where('id', $id)->first();

        $this->handleRelationships($relationships, $model);

        return ['data' => $model, 'error' => ''];
    }

    public function handleRelationships($relationships, $model)
    {
        foreach ($relationships as $relationship) {

            $model[$relationship["table"]];

            foreach ($relationship["unsets"] as $unset) {

                if (isset($model[$relationship["table"]][0])) {

                    foreach ($model[$relationship["table"]] as $val) {
                        unset($val[$unset]);
                    }
                } else if (isset($model[$relationship["table"]][$unset])) {

                    unset($model[$relationship["table"]][$unset]);
                }
            }

            if (isset($relationship["children"])) {

                foreach ($model[$relationship["table"]] as $table) {

                    $this->handleRelationships($relationship["children"], $table);
                }
            }
        }
    }

    public function getByColumn($columnName, $columnValue, $return = null)
    {
        if (!$this->getStruct()->where($columnName, $columnValue)->exists()) {
            return ['data' => null, 'error' => 'Does not exist'];
        }

        $model = $return ? $this->select($return)->where($columnName, $columnValue)->get() : $this->getStruct()->where($columnName, $columnValue)->get();

        return ['data' => $model, 'error' => ''];
    }

    protected function updateByColumn($column, $allInputs)
    {
        $model = $this->where($column, $allInputs[$column]);
        if (!isset($model)) {
            return ['error' => 'Error while updating', 'data' => null];
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
            return ['error' => 'Error while updating', 'data' => null];
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
            return ['error' => 'Error while updating the password', 'data' => null];
        }

        $password  = $model->password;

        if ($oldPassword != $password) {

            return ['error' => 'Incorrect password, please try again', "data" => [],];
        }

        $model->password = $newPassword;
        $model->publicKey = null;
        $model->save();

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function resetPassword($id, $newPassword)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Error while updating the password', 'data' => null];
        }

        $model->password = $newPassword;
        $model->publicKey = null;
        $model->save();

        $model = $this->get($id);
        $model["data"]["password"] = BaseController::BUS_LOCATOR_RESET_PASSWORD;

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function verifyEmail($emailVerificationToken, $status)
    {
        if (!$this->isExist($this->select('id')->where('emailVerificationToken', $emailVerificationToken))) {
            return ['error' => 'User not found, please register again', 'data' => null];
        }

        $id = $this->select('id')->where('emailVerificationToken', $emailVerificationToken)->first()['id'];

        $model = static::find($id);

        if ($model->verified > 0) {
            return ["error" => "This email has already been verified, please login.", "data" => []];
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
            return ['error' => 'Error while updating the status', 'data' => null];
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
            return ['error' => 'User does not exist', 'data' => null];
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

        $model->publicKey = null;
        $model->save();

        return ["data" => ["logout" => true], "error" => ""];
    }
}
