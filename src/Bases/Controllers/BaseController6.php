<?php

namespace Rashtel\Controllers;

use Rashtel\Domain\MailHandler;
use Rashtell\Domain\CodeLibrary;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\KeyManager;
use Rashtell\Domain\MCrypt;
use Rashtell\Domain\JSON;
use Rashtel\Models\BaseModel;
use Rashtel\Models\TransactionModel;

class BaseController
{

    private const EMAIL_VERIFIED = 1;
    private const USER_VERIFIED = 2;
    private const Rashtel_RESET_PASSWORD = 'Rashtel12345';

    protected function getValidJsonOrError($request)
    {
        $json = new JSON();

        $data = $request->getParsedBody();
        $data = isset($data) ? $data : $request->getBody();


        $validJson = $json->jsonFormat($data);

        if ($validJson == NULL) {
            $error = array('errorMessage' => 'The parameter is not a valid objects', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $error, 'data' => []];
        }

        if (!isset($validJson->data)) {
            $error = array('errorMessage' => 'The request object does not conform to standard', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $error, 'data' => []];
        }

        return ['data' => $validJson->data, 'error' => ""];
    }

    protected function getPageNumOrError($request)
    {
        $data = $request->getAttributes();
        $page = 1;

        if (!(isset($data['page']))) {
            // $error = array('errorMessage' => 'Page is required', 'errorStatus' => 1, 'statusCode' => 400);

            // return ['error' => $error, 'page' => null];
            $page = 1;
        }

        $page = $data['page'];

        if (!(is_numeric($page) || (int) $page < 0)) {
            // $error = array('errorMessage' => 'The page number is invalid', 'errorStatus' => 1, 'statusCode' => 400);

            // return ['error' => $error, 'page' => null];
            $page = 1;
        }

        return ['page' => $page, 'error' => null];
    }

    protected function getPageLimit($request)
    {
        $data = $request->getAttributes();

        $limit = isset($data['limit']) && is_numeric($data['limit']) ? $data['limit'] : 10;

        ['page' => $page, 'error' => $error] = $this->getPageNumOrError($request);
        // $start = ($page - 1) * $limit;

        return ['limit' => $limit, 'error' => $error];
    }

    protected function getDateOrError($request)
    {
        $data = $request->getAttributes();

        if (!(isset($data['fromDate']) and isset($data['toDate']))) {
            $error = array('errorMessage' => 'Date range is required', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $error, 'page' => []];
        }

        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];

        if (!(is_numeric($fromDate) || is_numeric($toDate))) {
            $error = array('errorMessage' => 'The date is invalid', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $error, 'page' => []];
        }

        return ['fromDate' => $fromDate, 'toDate' => $toDate, 'error' => ""];
    }

    protected function getRouteParams($request, $details)
    {
        $data = $request->getAttributes();

        $existData = ['error' => null];

        foreach ($details as $detail) {
            if (!isset($data[$detail])) {

                $error = array('errorMessage' => 'Invalid request: ' . $detail . " not set", 'errorStatus' => 1, 'statusCode' => 400);

                $existData = array_merge($existData, ['error' => $error]);
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
            $error = array('errorMessage' => 'Invalid url', 'errorStatus' => 1, 'statusCode' => 400);
            return ["error" => $error, "digest" => ""];
        }

        $digest = $request->getAttributes()['digest'];

        return ["data" => $digest, "error" => ""];
    }

    protected function valuesExistsOrError($data, array $details = [])
    {
        $existData = ['error' => null];

        foreach ($details as $detail) {
            if (!isset($data->$detail)) {
                $json = new JSON();

                $error = array('errorMessage' => 'All fields are required: ' . $detail . " not set", 'errorStatus' => 1, 'statusCode' => 400);

                $existData = array_merge($existData, ['error' => $error, 'username' => null, 'password' => null]);
                return $existData;
            }

            $existData = array_merge($existData, [$detail => $data->$detail]);
        }

        return $existData;
    }

    public static function getTokenInputsFromRequest($request)
    {
        $token = static::getToken($request);

        if (!$token) {
            return [];
        }

        $authDetails = (new BaseModel)->getTokenInputs($token);

        return $authDetails;
    }

    public static function getToken($request)
    {
        $headers = $request->getHeaders();

        $authorization = (isset($headers['Token']) && $headers['Token']) ?? (isset($headers['token']) && $headers['token']) ?? null;

        if (!$authorization) {
            return null;
        }

        $token = $authorization[0];

        $token = explode(' ', $token)[1];

        return $token;
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

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param Model $model
     * @param Array $inputs
     * @param Arrat $options = ['isAccount'=>:Boolean, 'sendMail'=>:Boolean, userType=>:MailHandler::USERTYPE]
     * 
     */

    public function createSelf(Request $request, ResponseInterface $response, $model, array $inputs, array $options = []): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

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

        $data = $model->createSelf($allInputs);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        $success = '';
        $error = '';
        if (isset($options['sendMail']) and $options['sendMail'] == true) {

            //Send and email with the digest
            $mail = new MailHandler(MailHandler::TEMPLATE_CONFIRM_EMAIL, $options["userType"], $allInputs["email"], ["username" => $allInputs["username"], "digest" => $digest]);

            ['error' => $error, 'success' => $success] = $mail->sendMail();
        }

        $payload = ['successMessage' => 'Create success ' . $success, 'statusCode' => 201, 'data' => $data['data'], 'errorMessage' => $error];

        return $json->withJsonResponse($response, $payload);
    }

    public function login(Request $request, ResponseInterface $response, $model, array $inputs): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            $logout = $this->logoutSelf($request, $response, $model, $inputs);

            return $json->withJsonResponse($response, $error);
        }

        $allInputs =  $this->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            $logout = $this->logoutSelf($request, $response, $model, $inputs);

            return $json->withJsonResponse($response, $allInputs['error']);
        }

        if ($allInputs['password'] == self::Rashtel_RESET_PASSWORD) {
            //TODO Redirect user to change password page
        }

        $kmg = new KeyManager();
        $password = $kmg->getDigest($allInputs['password']);

        $cLib = new CodeLibrary();
        $public_key = $cLib->genID(12, 1);

        $allInputs['password'] = $password;
        $allInputs['public_key'] = $public_key;


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

    public function getAll(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['page' => $page, 'error' => $error] = $this->getPageNumOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        ['limit' => $limit, 'error' => $error] = $this->getPageLimit($request);

        $data = $model::getAll($page, $limit);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => '1', 'stautsCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => 'Request success', 'stautsCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getSelf(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ['id' => $id] = $authDetails;

        $data = $model->get($id);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 200);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => 'Request success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function getById(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['id' => $id, 'error' => $error] = $this->getRouteParams($request, ["id"]);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->get($id);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 200);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => 'Requst success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    /**
     * @params id, point, type: add or sub, transactionType
     */
    public function updatePoint(Request $request, ResponseInterface $response, $model, array $inputs, array $options): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $allInputs["type"] = $options["type"];
        $allInputs["transactionType"] = $options["transactionType"];
        $allInputs["userId"] = $options["userId"];

        $data = $model::updatePoint($allInputs);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ['successMessage' => 'Load points success', 'statusCode' => 201, 'data' => $data['data'], 'errorMessage' => $error];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateSelf(Request $request, ResponseInterface $response, $model, array $inputs): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        if ($allInputs['id'] != $authDetails['id']) {
            $error = ['errorMessage' => 'You do not have sufficient priveleges to perform this action', 'errorStatus' => 1, 'statusCode' => 401];

            return $json->withJsonResponse($response,  $error);
        }

        $data = $model->updateSelf($allInputs);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateById(Request $request, ResponseInterface $response, $model, array $inputs): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $data = $model->updateById($allInputs);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response, $payload);
    }

    public function updatePassword(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, ['newPassword', 'oldPassword']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['newPassword' => $newPassword, 'oldPassword' => $oldPasswod, 'error' => $error] = $allInputs;

        $kmg = new KeyManager();

        $newPassword = $kmg->getDigest($newPassword);
        $oldPasswod = $kmg->getDigest($oldPasswod);

        $public_key =  $authDetails['public_key'];
        $id = $authDetails['id'];

        $data = $model->updatePassword($id, $newPassword, $oldPasswod);

        if ($data['error']) {
            $this->logoutSelf($request, $response, $model);

            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = ['successMessage' => 'Password update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response,  $payload);
    }

    public function updateSelfColumns(Request $request, ResponseInterface $response, $model, array $columnNames): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, $columnNames);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        unset($allInputs["error"]);

        $id = $allInputs['id'];

        unset($allInputs["id"]);

        $data = $model->updateColumns($id, $allInputs);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = ['successMessage' => 'Update success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response,  $payload);
    }

    public function resetPassword(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id, 'error' => $error] = $allInputs;
        $newPassword = self::Rashtel_RESET_PASSWORD;

        $kmg = new KeyManager();
        $encryptedPassword = $kmg->getDigest($newPassword);

        $password = $encryptedPassword;

        $data = $model->resetPassword($id, $password);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = ['successMessage' => 'Password reset success', 'statusCode' => 201, 'data' => $data['data']];

        return $json->withJsonResponse($response,  $payload);
    }

    public function verifyEmail(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['data' => $digest, 'error' => $error] = $this->getDigestOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $status = self::EMAIL_VERIFIED;

        $data = $model->verifyEmail($digest, $status);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406, 'data' => []];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ['successMessage' => 'Email verification success', 'statusCode' => 200, 'data' => $data['data']];

        //TODO redirect to login
        return $json->withJsonResponse($response, $payload);
    }

    public function verifyUser(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id, 'error' => $error] = $allInputs;

        $status = $this->USER_VERIFIED;

        $data = $model->verifyUser($id, $status);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 406, 'data' => []];

            return $json->withJsonResponse($response, $error);
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
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array('successMessage' => 'Delete success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function deleteById(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id, 'error' => $error] = $allInputs;

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->deleteById($id);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array('successMessage' => 'Delete success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    public function logoutSelf(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ["id" => $id] = $authDetails;
        $data = $model->logout($id);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array('successMessage' => 'Logout success', 'statusCode' => 200, 'data' => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function logoutById(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, ['id']);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        ['id' => $id] = $allInputs;

        $data = $model->logout($id);

        if ($data['error']) {
            $error = ['errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array('successMessage' => 'Logout success', 'statusCode' => 200, 'data' => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }
}
