<?php

namespace LAGOS_WASTE\Controllers;

use DateTime;
use LAGOS_WASTE\Domain\MailHandler;
use Rashtell\Domain\CodeLibrary;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\KeyManager;
use Rashtell\Domain\MCrypt;
use Rashtell\Domain\JSON;
use LAGOS_WASTE\Models\BaseModel;

class BaseController
{

    private const EMAIL_VERIFIED = 1;
    private const USER_VERIFIED = 2;
    private const DEFAULT_RESET_PASSWORD = 'lagos_waste_12345';

    protected function getValidJsonOrError($request)
    {
        $json = new JSON();

        $data = $request->getParsedBody();
        $data = isset($data) ? $data : $request->getBody();


        $validJson = $json->jsonFormat($data);

        if ($validJson == NULL) {
            $errorObj = array('errorMessage' => 'The parameter is not a valid objects', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $errorObj, 'data' => []];
        }

        if (!isset($validJson->data)) {
            $errorObj = array('errorMessage' => 'The request object does not conform to standard', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $errorObj, 'data' => []];
        }

        return ['data' => $validJson->data, 'error' => ""];
    }

    protected function getPageNumOrError($request)
    {
        $data = $request->getAttributes();

        $page = isset($data['page']) ? $data['page'] : 1;

        $page = (!is_numeric($page) || (int) $page < 0) ? 1 : $page;

        return ['page' => $page, 'error' => []];
    }

    protected function getDateOrError($request)
    {
        $data = $request->getAttributes();

        if (!(isset($data['fromDate']) and isset($data['toDate']))) {
            $errorObj = array('errorMessage' => 'Date range is required', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $errorObj, 'page' => []];
        }

        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];

        if (!(is_numeric($fromDate) || is_numeric($toDate))) {
            $errorObj = array('errorMessage' => 'The date is invalid', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $errorObj, 'page' => []];
        }

        return ['fromDate' => $fromDate, 'toDate' => $toDate, 'error' => ""];
    }

    protected function getPageLimit($request)
    {
        $data = $request->getAttributes();

        $limit = isset($data['limit']) && is_numeric($data['limit']) ? $data['limit'] : 1000;

        ['page' => $page, 'error' => $errorObj] = (new BaseController)->getPageNumOrError($request);
        // $start = ($page - 1) * $limit;

        return ['limit' => $limit, 'error' => $errorObj];
    }

    protected function getIdOrError($request)
    {
        $id = null;

        if (isset($request->getAttributes()['id'])) {
            $data = $request->getAttributes();
            $id = $data['id'];
        } elseif (isset($request->getParsedBody()['data']['id'])) {
            $data = $request->getParsedBody();
            $id = $data['data']['id'];
        } elseif (isset(BaseController::getTokenInputsFromRequest($request)['id'])) {
            // ['id' => $id] = BaseController::getTokenInputsFromRequest($request);
        }

        if (!is_numeric($id)) {
            $errorObj = array('errorMessage' => 'Invalid id parameter', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $errorObj, 'id' => $id];
        }

        return ['id' => $id, 'error' => ""];
    }

    protected function getRouteParams($request, $details)
    {
        $data = $request->getAttributes();

        $existData = ['error' => null];

        foreach ($details as $detail) {
            if (!isset($data[$detail])) {

                $errorObj = array('errorMessage' => 'Invalid request: ' . $detail . " not set", 'errorStatus' => 1, 'statusCode' => 400);

                $existData = array_merge($existData, ['error' => $errorObj]);
                return $existData;
            }

            $existData = array_merge($existData, [$detail => $data[$detail]]);
        }

        return $existData;

        // return $request->getAttributes();
    }

    protected function getDigestOrError($request)
    {
        if (!isset($request->getAttributes()['digest'])) {
            $errorObj = array('errorMessage' => 'Invalid url', 'errorStatus' => 1, 'statusCode' => 400);
            return ["error" => $errorObj, "digest" => ""];
        }

        $digest = $request->getAttributes()['digest'];

        return ["data" => $digest, "error" => null];
    }

    protected function valuesExistsOrError($data, array $details = [])
    {
        $existData = ['error' => null];

        foreach ($details as $detail) {
            if (!isset($data->$detail)) {

                $errorObj = array('errorMessage' => 'All fields are required: ' . $detail . " not set", 'errorStatus' => 1, 'statusCode' => 400);

                $existData = array_merge($existData, ['error' => $errorObj,]);

                return $existData;
            }

            $existData = array_merge($existData, [$detail => $data->$detail]);
        }

        foreach ($data as $key => $value) {
            $existData[$key] = $value;
        }

        return $existData;
    }

    public static function getTokenInputsFromRequest($request)
    {
        $token = static::getToken($request);

        if (!$token) {
            return [];
        }

        $authDetails = (new BaseModel())->getTokenInputs($token);

        return $authDetails;
    }

    public static function getToken($request)
    {
        $headers = $request->getHeaders();

        $authorization = isset($headers['Token']) ? $headers['Token'] : (isset($headers['token']) ? $headers['token'] : (isset($headers['Authorization']) ? $headers['Authorization'] : null));

        if (!$authorization) {
            return null;
        }

        $token = $authorization[0];

        $token = explode(' ', $token)[1];

        return $token;
    }

    public function parseImage($data)
    {
        if (isset($data->image)) {
            $image = $data->image;
            $imagePath = "assets/images/users/";
            $imageName = (new DateTime())->getTimeStamp();
            $newImage = "$imagePath$imageName.jpg";
            file_put_contents($newImage, base64_decode($image));
            $data->image = $newImage;
        }

        return $data;
    }

    public function appendSecurity($allInputs, $options)
    {
        $password = null;
        $public_key = null;
        $digest = null;
        if (isset($options["isAccount"]) and $options["isAccount"] == true) {

            $kmg = new KeyManager();
            $mcrypt = new MCrypt();

            $password = $kmg->getDigest($allInputs['password']);
            $public_key = $mcrypt->mCryptThis(time() * rand(55555, 999999999));

            $digest = $mcrypt->mCryptThis(time() * rand(111111111, 999999999));
        }

        $password != null && $allInputs["password"] = $password;
        $public_key != null && $allInputs["public_key"] = $public_key;
        $digest != null && $allInputs["digest"] = $digest;

        return $allInputs;
    }

    public function sendMail($allInputs)
    {
        $success = '';
        $error = '';
        if (isset($allInputs["email"]) and isset($options['sendMail']) and $options['sendMail'] == true) {

            //Send and email with the digest
            $mail = new MailHandler(MailHandler::TEMPLATE_CONFIRM_EMAIL, $options["userType"], $allInputs["email"], ["username" => $allInputs["name"], "digest" => $allInputs["digest"]]);

            ['error' => $error, 'success' => $success] = $mail->sendMail();
        }

        return ["success" => $success, "error" => $error];
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param Model $model
     * @param Array $inputs
     * @param Arrat $options = ['isAccount'=>:Boolean, 'sendMail'=>:Boolean, userType=>:MailHandler::USERTYPE]
     * 
     */

    public function createSelf(Request $request, ResponseInterface $response, $model, array $inputs, array $options = [], array $override = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $allInputs =  (new BaseController())->valuesExistsOrError($data, $inputs);

        if (isset($allInputs['error']) and $allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $allInputs = (new BaseController())->appendSecurity($allInputs, $options);

        foreach ($override as $key => $value) {
            $allInputs[$key] = $value;
        }

        $data = $model->createSelf($allInputs);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response,   $errorObj);
        }

        $mailResponse = $this->sendMail($allInputs);

        $payload = ['successMessage' => 'Created successesfully ' . $mailResponse["success"], 'statusCode' => 201, 'data' => $data['data'], 'errorMessage' => $mailResponse["error"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function createMany(Request $request, ResponseInterface $response, $model, array $inputs, array $options = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = $this->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $returnData = [];

        foreach ($data as $key => $eachData) {

            $eachData = $this->parseImage($eachData);

            $allInputs = $this->valuesExistsOrError($eachData, $inputs);

            if ($allInputs['error']) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }

            $allInputs = $this->appendSecurity($allInputs, $options);

            $modelData = $model->createSelf($allInputs);

            if ($modelData['error']) {
                $errorObj = ['errorMessage' => $modelData['error'], 'errorStatus' => 1, 'statusCode' => 406];

                $returnData[$key] = $modelData["error"];

                continue;
            }

            $mailResponse = $this->sendMail($allInputs);

            $returnData[$key] = $modelData["data"];
        }

        $payload = ['successMessage' => 'All created successfully. ', 'statusCode' => 201, 'data' => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function login(Request $request, ResponseInterface $response, $model, array $inputs, $override = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            $logout =  (new BaseController())->logoutSelf($request, $response, $model, $inputs);

            return $json->withJsonResponse($response,  $errorObj);
        }

        $allInputs =   (new BaseController())->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            $logout =  (new BaseController())->logoutSelf($request, $response, $model, $inputs);

            return $json->withJsonResponse($response, $allInputs['error']);
        }

        if ($allInputs['password'] == self::DEFAULT_RESET_PASSWORD) {
            //TODO Redirect user to change password page
        }

        $kmg = new KeyManager();
        $password = $kmg->getDigest($allInputs['password']);

        $cLib = new CodeLibrary();
        $public_key = $cLib->genID(12, 1);

        $allInputs['password'] = $password;
        $allInputs['public_key'] = $public_key;

        foreach ($override as $key => $value) {
            $allInputs[$key] = $value;
        }

        $data = $model->login($allInputs);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 401);

            return $json->withJsonResponse($response, $payload);
        }

        $token = (new KeyManager)->createClaims(json_decode($data['data'], true));

        unset($data["data"]["public_key"]);

        $payload = array('successMessage' => 'Login successful', 'statusCode' => 200, 'data' => $data['data'], 'token' => $token);

        return $json->withJsonResponse($response, $payload)->withHeader('token', 'bearer ' . $token);
    }

    public function getSelfDashboard(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ["id" => $id] = $authDetails;

        $data = $model->getSelfDashboard($id);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => 'Dashboard request success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getDashboard(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $data = $model->getDashboard();

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => 'Dashboard request success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getAll(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['page' => $page, 'error' => $errorObj] = (new BaseController)->getPageNumOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        ['limit' => $limit, 'error' => $errorObj] =  (new BaseController())->getPageLimit($request);

        $data = $model::getAll($page, $limit);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => '1', 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByDate(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['from' => $from, 'to' => $to, 'error' => $errorObj] = $this->getRouteParams($request, ["from", "to"]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->getByDate($from, $to);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => '1', 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByDateWithRelationship(Request $request, ResponseInterface $response, $model, $relationships): ResponseInterface
    {
        $json = new JSON();

        ['from' => $from, 'to' => $to, 'error' => $errorObj] = $this->getRouteParams($request, ["from", "to"]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->getByDateWithRelationship($from, $to, $relationships);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => '1', 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getSelf(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ['id' => $id] = $authDetails;

        $data = $model->get($id);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getById(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['id' => $id, 'error' => $errorObj] =  (new BaseController())->getRouteParams($request, ["id"]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->get($id);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByIdWithRelationships(Request $request, ResponseInterface $response, $model, $relationships): ResponseInterface
    {
        $json = new JSON();

        ['id' => $id, 'error' => $errorObj] = $this->getRouteParams($request, ["id"]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->getWithRelationships($id, $relationships);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByColumn(Request $request, ResponseInterface $response, $model, $columnName): ResponseInterface
    {
        $json = new JSON();

        [$columnName => $columnValue, 'error' => $errorObj] = $this->getRouteParams($request, [$columnName]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->getByColumn($columnName, $columnValue);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByColumns(Request $request, ResponseInterface $response, $model, $columnNames = []): ResponseInterface
    {
        $json = new JSON();

        //TODO spread out the column names
        $columnName = "";
        [$columnName => $columnValue, 'error' => $errorObj] = $this->getRouteParams($request, [$columnName]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->getByColumn($columnName, $columnValue);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function searchByColumn(Request $request, ResponseInterface $response, $model, $columnName): ResponseInterface
    {
        $json = new JSON();

        [$columnName => $columnValue, 'error' => $errorObj] = $this->getRouteParams($request, [$columnName]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->searchByColumn($columnName, $columnValue);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function searchByColumns(Request $request, ResponseInterface $response, $model, $columnNames): ResponseInterface
    {
        $json = new JSON();

        foreach ($columnNames as $columnName) {
            //TODO       
        }

        [$columnName => $columnValue, 'error' => $errorObj] = $this->getRouteParams($request, [$columnName]);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $data = $model->searchByColumn($columnName, $columnValue);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => '', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }


    /**
     * @params id, point, type: add or sub, transactionType
     */
    public function updatePoint(Request $request, ResponseInterface $response, $model, array $inputs, array $options, $override = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $allInputs =  (new BaseController())->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $allInputs["type"] = $options["type"];
        $allInputs["transactionType"] = $options["transactionType"];
        $allInputs["userId"] = $options["userId"];

        $data = $model::updatePoint($allInputs);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'Load points success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateSelf(Request $request, ResponseInterface $response, $model, array $inputs, $override = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs =  (new BaseController())->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $allInputs['id'] = $authDetails['id'];

        // if ($allInputs['id'] != $authDetails['id']) {
        //     $errorObj = ['errorMessage' => 'You do not have sufficient priveleges to perform this action', 'errorStatus' => 1, 'statusCode' => 401];

        //     return $json->withJsonResponse($response,  $errorObj);
        // }

        foreach ($override as $key => $value) {
            $allInputs[$key] = $value;
        }

        $data = $model->updateSelf($allInputs);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateManySelf(Request $request, ResponseInterface $response, $model, array $inputs): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = $this->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $returnData = [];
        foreach ($data as $key => $eachData) {

            $eachData = $this->parseImage($eachData);


            $allInputs = $this->valuesExistsOrError($eachData, $inputs);

            if ($allInputs['error']) {
                $returnData[$key] = $allInputs['error'];
                continue;

                // return $json->withJsonResponse($response, $allInputs['error']);
            }

            if ($allInputs['id'] != $authDetails['id']) {
                $errorMessage =  'You do not have sufficient priveleges to perform this action';

                $errorObj = ['errorMessage' => $errorMessage, 'errorStatus' => 1, 'statusCode' => 401];

                $returnData[$key] = $errorMessage;

                return $json->withJsonResponse($response,  $errorObj);
            }

            $modelData = $model->updateSelf($allInputs);

            if ($modelData['error']) {
                $errorObj = ['errorMessage' => $modelData['error'], 'errorStatus' => 1, 'statusCode' => 406];

                return $json->withJsonResponse($response,   $errorObj);
            }

            $returnData[$key] = $modelData['data'];
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateById(Request $request, ResponseInterface $response, $model, array $inputs, $override = [], $condition = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs =  (new BaseController())->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        foreach ($override as $key => $value) {
            $allInputs[$key] = $value;
        }

        if (count($condition) > 0) {
            $data = $model->updateByIdWithCondition($allInputs, $condition);
        } else {
            $data = $model->updateById($allInputs);
        }

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateManyById(Request $request, ResponseInterface $response, $model, array $inputs): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = $this->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $returnData = [];
        foreach ($data as $key => $eachData) {

            $eachData = $this->parseImage($eachData);

            $allInputs = $this->valuesExistsOrError($eachData, $inputs);

            if ($allInputs['error']) {
                $returnData[$key] = $allInputs['error'];

                continue;

                // return $json->withJsonResponse($response, $allInputs['error']);
            }

            $modelData = $model->updateById($allInputs);

            if ($modelData['error']) {
                $errorObj = ['errorMessage' => $modelData['error'], 'errorStatus' => 1, 'statusCode' => 406];

                $returnData[$key] = $modelData['error'];
                continue;

                // return $json->withJsonResponse($response, $errorObj);
            }

            $returnData[$key] = $modelData["data"];
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateByColumn(Request $request, ResponseInterface $response, $model, array $inputs, $columnName): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = $this->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $data = $model->updateByColumn($columnName, $allInputs);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateManyByColumn(Request $request, ResponseInterface $response, $model, array $inputs, $columnName): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = $this->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $returnData = [];
        foreach ($data as $key => $eachData) {

            $eachData = $this->parseImage($eachData);

            $allInputs = $this->valuesExistsOrError($eachData, $inputs);

            if ($allInputs['error']) {
                $returnData[$key] = $allInputs['error'];
                continue;

                // return $json->withJsonResponse($response, $allInputs['error']);
            }

            $modelData = $model->updateByColumn($columnName, $allInputs);

            if ($modelData['error']) {
                $errorObj = ['errorMessage' => $modelData['error'], 'errorStatus' => 1, 'statusCode' => 406];

                $returnData[$key] = $modelData['error'];

                // return $json->withJsonResponse($response, $errorObj);
            }

            $returnData[$key] = $modelData["data"];
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateSelfColumns(Request $request, ResponseInterface $response, $model, array $columnNames): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs =  (new BaseController())->valuesExistsOrError($data, $columnNames);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        unset($allInputs["error"]);

        $id = $allInputs['id'];

        unset($allInputs["id"]);

        $data = $model->updateColumns($id, $allInputs);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response,  $payload);
    }

    public function updatePassword(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs =  (new BaseController())->valuesExistsOrError($data, ['newPassword', 'oldPassword']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['newPassword' => $newPassword, 'oldPassword' => $oldPasswod, 'error' => $errorObj] = $allInputs;

        $kmg = new KeyManager();

        $newPassword = $kmg->getDigest($newPassword);
        $oldPasswod = $kmg->getDigest($oldPasswod);

        $public_key =  $authDetails['public_key'];
        $id = $authDetails['id'];

        $data = $model->updatePassword($id, $newPassword, $oldPasswod);

        if ($data['error']) {
            (new BaseController())->logoutSelf($request, $response, $model);

            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'Password update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response,  $payload);
    }

    public function resetPassword(Request $request, ResponseInterface $response, $model, $condition = []): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $allInputs =  (new BaseController())->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id, 'error' => $errorObj] = $allInputs;
        $newPassword = self::DEFAULT_RESET_PASSWORD;

        $kmg = new KeyManager();
        $encryptedPassword = $kmg->getDigest($newPassword);

        $password = $encryptedPassword;

        if (count($condition)) {
            $data = $model->resetPasswordWithCondition($id, $password, $condition);
        } else {
            $data = $model->resetPassword($id, $password);
        }

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'Password reset success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response,  $payload);
    }


    public function verifyEmail(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['data' => $digest, 'error' => $errorObj] =  (new BaseController())->getDigestOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $status = self::EMAIL_VERIFIED;

        $data = $model->verifyEmail($digest, $status);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406, 'data' => []];

            return $json->withJsonResponse($response,   $errorObj);
        }

        $payload = ['successMessage' => 'Email verification success', 'statusCode' => 200, 'data' => $data['data']];

        //TODO redirect to login
        return $json->withJsonResponse($response, $payload);
    }

    public function verifyUser(Request $request, ResponseInterface $response, $model, $condition = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs =  (new BaseController())->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id, 'error' => $errorObj] = $allInputs;

        $status = self::USER_VERIFIED;

        if (count($condition) > 0) {
            $data = $model->verifyUserWithCondition($id, $status, $condition);
        } else {
            $data = $model->verifyUser($id, $status);
        }

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406, 'data' => []];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = ['successMessage' => 'User verification success', 'statusCode' => 200, 'data' => $data['data']];

        return $json->withJsonResponse($response, $payload);
    }

    public function deleteSelf(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ['id' => $id] = $authDetails;

        $data = $model->deleteById($id);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = array('successMessage' => 'Delete success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function deleteById(Request $request, ResponseInterface $response, $model, $condition = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs =  (new BaseController())->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id, 'error' => $errorObj] = $allInputs;

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        if (count($condition) > 0) {
            $data = $model->deleteByIdWithCondition($id, $condition);
        } else {
            $data = $model->deleteById($id);
        }

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = array('successMessage' => 'Delete success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function logoutSelf(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $authDetails = isset($authDetails["id"]) ? $authDetails : ["id" => 0];
        ["id" => $id] = $authDetails;
        $data = $model->logout($id);

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = array('successMessage' => 'Logout success', 'statusCode' => 200, 'data' => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function logoutById(Request $request, ResponseInterface $response, $model, $condition = []): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ['data' => $data, 'error' => $errorObj] = (new BaseController())->getValidJsonOrError($request);

        if ($errorObj) {
            return $json->withJsonResponse($response,  $errorObj);
        }

        $allInputs =  (new BaseController())->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id] = $allInputs;

        if (count($condition) > 0) {
            $data = $model->logoutWithCondition($id, $condition);
        } else {
            $data = $model->logout($id);
        }

        if ($data['error']) {
            $errorObj = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $errorObj);
        }

        $payload = array('successMessage' => 'Logout success', 'statusCode' => 200, 'data' => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }
}
