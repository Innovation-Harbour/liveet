<?php

namespace LAMATA_EPURSE\Controllers;

use LAMATA_EPURSE\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use LAMATA_EPURSE\Models\OrganizationModel;
use Rashtell\Domain\CodeLibrary;
use Rashtell\Domain\JSON;
use Rashtell\Domain\KeyManager;

class OrganizationController extends BaseController
{
    public function authenticateOrganization(Request $request, ResponseInterface $response)
    {
        $json = new JSON();

        $inputs = ["publicKey"];
        $model = (new OrganizationModel());

        ['data' => $data, 'error' => $error] = (new OrganizationController())->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs =  (new OrganizationController())->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $data = $model->authenticatePublicKey($allInputs);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400, 'data' => null);

            return $json->withJsonResponse($response, $payload);
        }

        $token = (new KeyManager())->createClaims(json_decode($data["data"], true));

        $payload = array('successMessage' => 'Authentication successful', 'statusCode' => 200, 'data' => $data['data'], 'token' => $token);

        return $json->withJsonResponse($response, $payload)->withHeader('token', 'bearer ' . $token);
    }

    public function createOrganization(Request $request, ResponseInterface $response)
    {
        return (new BaseController)->create($request, $response, new OrganizationModel(), ['name', 'phone', 'email', 'address'], ['isAccount' => false, 'sendMail' => true, 'userType' => MailHandler::USER_TYPE_ORGANIZATION]);
    }

    public function getOrganizations(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getAll($request, $response, new OrganizationModel());
    }

    public function loginOrganization(Request $request, ResponseInterface $response)
    {

        $json = new JSON();

        $inputs = ["email", "password"];
        $model = (new OrganizationModel());

        ['data' => $data, 'error' => $error] = (new OrganizationController())->getValidJsonOrError($request);


        if ($error) {

            return $json->withJsonResponse($response, $error);
        }

        $allInputs =  (new OrganizationController())->valuesExistsOrError($data, $inputs);

        if ($allInputs['error']) {

            return $json->withJsonResponse($response, $allInputs['error']);
        }

        if ($allInputs['password'] == self::LAMATA_EPURSE_RESET_PASSWORD) {
            //TODO Redirect organization to change password page
        }

        $kmg = new KeyManager();
        $password = $kmg->getDigest($allInputs['password']);

        $cLib = new CodeLibrary();
        $publicKey = $cLib->genID(12, 1);

        $allInputs['password'] = $password;
        $allInputs['publicKey'] = $publicKey;

        $data = $model->login($allInputs);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 401, 'data' => null);

            return $json->withJsonResponse($response, $payload);
        }

        $token = (new KeyManager())->createClaims(json_decode($data["data"], true));

        if (isset($data["organizations"])) {
            $data["data"]["organizations"] = $data["organizations"];
        }

        unset($data["data"]["publicKey"]);

        $payload = array('successMessage' => 'Login successful', 'statusCode' => 200, 'data' => $data['data'], 'token' => $token);

        return $json->withJsonResponse($response, $payload)->withHeader('token', 'bearer ' . $token);
    }

    public function getOrganization(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getSelf($request, $response, new OrganizationModel());
    }

    public function logoutOrganization(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->logoutSelf($request, $response, new OrganizationModel());
    }
}
