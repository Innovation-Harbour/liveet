<?php

namespace LAGOS_RECYCLE\Models;

use Rashtell\Domain\KeyManager;
use Illuminate\Database\Eloquent\Model;
use LAGOS_RECYCLE\Domain\Constants;
use LAGOS_RECYCLE\Domain\PointsManager;
use Rashtell\Domain\MCrypt;

class BaseModel extends Model
{
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
            $isCreate = false;
        }

        foreach ($required as $key) {
            if (!isset($details[$key])) {
                return ["error" => $key . " is required"];
            }

            if (!$details[$key]) {
                return ["error" => $details[$key] . " is invalid"];
            }

            if (in_array($key, $existExceptions)) {
                continue;
            }

            if ($isCreate) {
                if (self::isExist($this->select('id')->where($key, $details[$key]))) {
                    return ['error' => $key . ' exists'];
                }
            } else {
                if (self::isExist($this::select('id')->where($key, $details[$key])->where('id', '!=', $details["id"]))) {
                    return ['error' => $key . ' exists'];
                }
            }
        }
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



    protected static function isExist($query): bool
    {

        return $query->exists();
    }

    protected static function getCount()
    {
        return static::count();
    }

    protected static function search($searchTerm)
    {
        return static::select();
    }

    public function getAll($page, $limit)
    {
        $start = ($page - 1) * $limit;

        if (!static::isExist(static::select('id')->where('id', '>', 0)->offset($start)->limit($limit))) {
            return ['data' => [], 'error' => 'No data'];
        }

        $allmodels = $this->getStruct()->where('id', '>', '0')->offset($start)->limit($limit)->get();

        $total = static::count();

        return ['data' => ["total" => $total, "all" => $allmodels,], 'error' => ''];
    }

    public function getByDate($from, $to)
    {
        if (!$this->isExist(static::select('id')->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to))) {
            return ['data' => [], 'error' => 'No more data'];
        }

        $allmodels = $this->getStruct()->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to)->get();

        return ['data' => $allmodels, 'error' => ''];
    }

    public function getByDateWithRelationship($from, $to, $relationships)
    {
        if (!$this->isExist(static::select('id')->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to))) {
            return ['data' => [], 'error' => 'No more data'];
        }

        $allmodels = $this->getStruct()->where('dateCreated', '>=', $from)->where("dateCreated", "<=", $to)->get();

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

    public function get($id)
    {
        if (!static::find($id)) {
            return ['data' => [], 'error' => 'Not found'];
        }

        $model = static::getStruct()->where('id', $id)->first();

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

    public function getWithRelationships($id, $relationships)
    {
        if (!static::find($id)) {
            return ['data' => [], 'error' => 'Does not exist'];
        }

        $model = $this->getStruct()->where('id', $id)->first();

        $this->handleRelationships($relationships, $model);

        return ['data' => $model, 'error' => ''];
    }

    public function getByColumnWithRelationships($columnName, $columnValue, $relationships)
    {
        if (!self::isExist($this->where($columnName, $columnValue))) {
            return ['data' => [], 'error' => 'Does not exist'];
        }

        $model = $this->getStruct()->where($columnName, $columnValue)->first();

        $this->handleRelationships($relationships, $model);

        return ['data' => $model, 'error' => ''];
    }

    public function getByColumn($columnName, $columnValue)
    {
        if (!$this->getStruct()->where($columnName, $columnValue)->exists()) {
            return ['data' => [], 'error' => 'Does not exist'];
        }

        $model = $this->getStruct()->where($columnName, $columnValue)->get();

        return ['data' => $model, 'error' => ''];
    }

    public function getByColumnWithCustomReturnColumns($columnName, $columnValue, $returnColumns = [])
    {
        if (!$this->select("id")->where($columnName, $columnValue)->exists()) {
            return ['data' => [], 'error' => 'Does not exist'];
        }

        $model = $this->select($returnColumns)->where($columnName, $columnValue)->get();

        return ['data' => $model, 'error' => ''];
    }

    public function searchByColumn($columnName, $columnValue)
    {
        // if (!$this->getStruct()->where($columnName, $columnValue)->exists()) {
        //     return ['data' => [], 'error' => 'Does not exist'];
        // }

        $model = $this->getStruct()->where($columnName, 'LIKE', '%' . $columnValue . '%')->get();

        return ['data' => $model, 'error' => ''];
    }

    public function getSelfTransactions($id)
    {
        $model =  self::find($id);

        if (!$model) {
            return ["data" => [], "error" => "Not found"];
        }

        $transactions = $model->transactions;

        if (sizeof($transactions) < 1) {
            return ["data" => [], "error" => "The user has no transaction yet"];
        }
        // $transactions->admin;
        // $transactions->organization;
        // $transactions->agent;
        // $transactions->externalAgent;
        // isset($transactions->customer) && $transactions->customer;

        // return ["data" => $transactions, "error" => null];

        return ["data" => (new BaseModel())->appendDetailsToTransaction($transactions), "error" => null];
    }

    protected function appendDetailsToTransaction($transactions)
    {
        $iterator = 0;
        $newTransactions = [];
        for ($iterator; $iterator < sizeof($transactions); $iterator++) {

            $newTransactions[$iterator] = $transactions[$iterator];

            $adminID = $transactions[$iterator]["adminID"];
            isset($transactions[$iterator]["adminID"]) && $newTransactions[$iterator]["admin"] = AdminModel::select("firstname", "lastname", "phone")->where("id", $adminID)->first();

            $organizationID = $transactions[$iterator]["organizationID"];
            isset($transactions[$iterator]["organizationID"]) && $newTransactions[$iterator]["organization"] = OrganizationModel::select("name", "phone")->where("id", $organizationID)->first();

            $agentID = $transactions[$iterator]["agentID"];
            isset($transactions[$iterator]["agentID"]) && $newTransactions[$iterator]["agent"] = AgentModel::select("firstname", "lastname", "phone")->where("id", $agentID)->first();

            $externalAgentID = $transactions[$iterator]["externalAgentID"];
            isset($transactions[$iterator]["externalAgentID"]) && $newTransactions[$iterator]["externalAgent"] = ExternalAgentModel::select("firstname", "lastname", "phone")->where("id", $externalAgentID)->first();

            $customerID = $transactions[$iterator]["customerID"];
            isset($transactions[$iterator]["customerID"]) && $newTransactions[$iterator]["customer"] = CustomerModel::select("firstname", "lastname", "phone")->where("id", $customerID)->first();
        }

        return $newTransactions;
    }

    public function getOrganization($id, $model = ExternalAgentModel::class)
    {
        $model =  $model::find($id);

        if (!$model) {
            return ["data" => [], "error" => "Not found"];
        }

        $organization = $model->organization;

        if (!$organization) {
            return ["data" => [], "error" => "User belongs to no organization"];
        }

        return ["data" => $organization, "error" => null];
    }

    public function getAgents($id)
    {
        $model = self::find($id);

        if (!$model) {
            return ['data' => [], 'error' => 'Not found'];
        }

        $agents = $model->agents->makeHidden(["password", "public_key", "digest", "deleted_at"]);;

        if (!$agents) {
            return ['data' => [], 'error' => 'No agents yet '];
        }


        return ['data' => $agents, 'error' => ''];
    }

    public function getExternalAgents($id)
    {
        $model = self::find($id);

        if (!$model) {
            return ['data' => [], 'error' => 'Not found'];
        }

        $externalAgents = $model->externalAgents->makeHidden(["password", "public_key", "digest", "deleted_at"]);;

        if (!$externalAgents) {
            return ['data' => [], 'error' => 'No external agents yet '];
        }


        return ['data' => $externalAgents, 'error' => ''];
    }

    public function getCustomers($id)
    {
        $model = self::find($id);

        if (!$model) {
            return ['data' => [], 'error' => 'Not found'];
        }

        $customers = $model->customers->makeHidden(["password", "public_key", "digest", "deleted_at"]);

        if (!$customers or $customers == "[]") {
            return ['data' => [], 'error' => 'No customers yet'];
        }

        return ['data' => ["all" => $customers, "total" => count($customers)], 'error' => ''];
    }

    public function getOrganizationAgent($organizationID, $agentID)
    {
        $model = self::find($organizationID);

        if (!$model) {
            return ['data' => [], 'error' => 'Organization does not exist'];
        }

        $agents = $model->agents;

        foreach ($agents as $agent) {

            if ($agent["id"] == $agentID) {
                return ['data' => $agent, 'error' => ''];
            }
        }

        return ['data' => [], 'error' => 'This agent does not exist for this organization'];
    }

    public function getOrganizationExternalAgent($organizationID, $externalAgentID)
    {
        $model = self::find($organizationID);

        if (!$model) {
            return ['data' => [], 'error' => 'Organization does not exist'];
        }

        $externalAgents = $model->externalAgents;

        foreach ($externalAgents as $externalAgent) {

            if ($externalAgent["id"] == $externalAgentID) {
                return ['data' => $externalAgent, 'error' => ''];
            }
        }

        return ['data' => [], 'error' => 'This external agent does not exist for this organization'];
    }

    public function getOrganizationAgentTransactions($organizationID, $agentID)
    {
        $model = self::find($organizationID);

        if (!$model) {
            return ['data' => [], 'error' => 'Organization does not exist'];
        }

        $agents = $model->agents;

        foreach ($agents as $agent) {

            if ($agent["id"] == $agentID) {
                $transactions = $agent->transactions;

                if (sizeof($transactions) == 0) {
                    return ["data" => [], "error" => "This agent has no transactions yet"];
                }

                return ["data" => $transactions, "error" => null];
            }
        }

        return ['data' => [], 'error' => 'This agent does not exist for this organization'];
    }

    public function getOrganizationExternalAgentTransactions($organizationID, $externalAgentID)
    {
        $model = self::find($organizationID);

        if (!$model) {
            return ['data' => [], 'error' => 'Organization does not exist'];
        }

        $externalAgents = $model->externalAgents;

        foreach ($externalAgents as $externalAgent) {

            if ($externalAgent["id"] == $externalAgentID) {

                $transactions = $externalAgent->transactions;

                if (sizeof($transactions) == 0) {
                    return ["data" => [], "error" => "This external agent has no transactions yet"];
                }
                return ["data" => $transactions, "error" => null];
            }
        }

        return ['data' => [], 'error' => 'This external agent does not exist for this organization'];
    }

    public function verifyEmail($digest, $status)
    {
        if (!self::isExist($this->select('id')->where('digest', $digest))) {
            return ['error' => 'User not found, please register again', 'data' => []];
        }

        $id = $this->select('id')->where('digest', $digest)->first()['id'];

        $model = static::find($id);

        if ($model->verified > 0) {
            return ["error" => "This email has already been verified, please login.", "data" => []];
        }

        $model->verified = $status;
        $model->save();

        $model = $this->select('id', 'username', 'firstname', 'lastname', 'email')->where("id", $id)->first();

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function verifyUser($id, $status)
    {
        $model = $this->find($id);
        if (!$model) {
            return ['error' => 'Error while updating the status', 'data' => []];
        }

        // if ($model->verified == 0) {
        //     return ['error' => 'Email not verified'];
        // }

        if ($model->verified > 1) {
            return ['error' => "User already verified"];
        }

        $model->verified = $status;
        $model->save();

        $model = $this->get($id);

        return ['data' => $model['data'], 'error' => $model['error']];
    }

    public function verifyUserWithCondition($id, $status, $condition)
    {
        $model = $this->find($id);

        if (!$model) {
            return ['error' => 'Not found', 'data' => []];
        }

        foreach ($condition as $key => $value) {
            if ($model[$key] != $value) {
                return ['error' => 'Not found', 'data' => []];
            }
        }

        return $this->verifyUser($id, $status);
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

    public function updateByIdWithCondition($details, $condition)
    {
        $id = $details['id'];
        $model = $this->find($id);

        if (!$model) {
            return ['error' => 'Not found', 'data' => []];
        }

        foreach ($condition as $key => $value) {
            if ($model[$key] != $value) {
                return ['error' => 'Not found', 'data' => []];
            }
        }

        return $this->updateById($details);
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

    protected function updatePoint($details)
    {
        $id = $details["id"];
        $point = $details["point"];
        $type = $details["type"];

        $model = $this->find($id);

        if (!$model) {
            return ['error' => 'Not found', 'data' => []];
        }

        $availablePoints = $model->availablePoints;

        ($type == Constants::UPDATE_POINT_ADD) && ($newAvailablePoints = (float) $availablePoints + (float) $point);

        ($type == Constants::UPDATE_POINT_SUBTRACT) && ($newAvailablePoints = (float) $availablePoints - (float) $point);

        $model->availablePoints = $newAvailablePoints;

        $model->save();


        $data = (new TransactionModel)->createUpdatePointTransaction($details);

        return ["data" => $data["data"], "error" => $data["error"]];
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

    public function resetPasswordWithCondition($id, $newPassword, $condition)
    {
        $model = $this->find($id);

        if (!$model) {
            return ['error' => 'Not found', 'data' => []];
        }

        foreach ($condition as $key => $value) {
            if ($model[$key] != $value) {
                return ['error' => 'Not found', 'data' => []];
            }
        }

        return $this->resetPassword($id, $newPassword);
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

    public function deleteByIdWithCondition($id, $condition)
    {
        $model = $this->find($id);

        if (!$model) {
            return ['error' => 'Not found', 'data' => []];
        }

        foreach ($condition as $key => $value) {
            if ($model[$key] != $value) {
                return ['error' => 'Not found', 'data' => []];
            }
        }

        return $this->deleteById($id);
    }

    public function logout($id)
    {
        $model = static::find($id);

        if (!$model) {
            // return ["error" => "Not found", "data" => []];
            return ["error" => "", "data" => []];
        }

        $model->public_key = null;
        $model->save();

        return ["data" => ["logout" => true], "error" => null];
    }

    public function logoutWithCondition($id, $condition)
    {

        $model = $this->find($id);

        if (!$model) {
            return ['error' => 'Not found', 'data' => []];
        }

        foreach ($condition as $key => $value) {
            if ($model[$key] != $value) {
                return ['error' => 'Not found', 'data' => []];
            }
        }

        return $this->logout($id);
    }
}
