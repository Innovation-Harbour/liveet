<?php

namespace BUS_LOCATOR\Controllers;

use BUS_LOCATOR\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use BUS_LOCATOR\Models\OrganizationModel;
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

        $data = $model->authenticateWithPublicKey($allInputs);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400, 'data' => null);

            return $json->withJsonResponse($response, $payload);
        }

        $token = (new KeyManager())->createClaims(json_decode($data["data"], true));

        $payload = array('successMessage' => 'Authentication successful', 'statusCode' => 200, 'data' => $data['data'], 'token' => $token);

        return $json->withJsonResponse($response, $payload)->withHeader('token', 'bearer ' . $token);
    }

    public function loginOrganization(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->login($request, $response, new OrganizationModel(), ['username', 'password']);
    }

    public function createOrganization(Request $request, ResponseInterface $response)
    {
        return (new BaseController)->create($request, $response, new OrganizationModel(), ['username', 'password', 'name', 'phone', 'email', 'address'], ['isAccount' => true, 'sendMail' => true, 'userType' => MailHandler::USER_TYPE_ORGANIZATION]);
    }

    public function getOrganizations(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getAll($request, $response, new OrganizationModel());
    }

    public function getOrganizationById(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getById($request, $response, new OrganizationModel());
    }

    public function getOrganizationByIdProperty(Request $request, ResponseInterface $response): ResponseInterface
    {
        ['property' => $property, 'error' => $error] = $this->getRouteParams($request, ["property"]);

        return (new BaseController)->getById($request, $response, new OrganizationModel(), $return = $property);
    }

    public function updateOrganizationByIdPublicKey(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        ['id' => $id, 'error' => $error] = $this->getRouteParams($request, ["id"]);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = (new OrganizationModel())->generateNewPublicKey(["id" => $id]);

        if ($data['error']) {
            $payload = array('errorMessage' => $data['error'], 'errorStatus' => 1, 'statusCode' => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array('successMessage' => 'Requst success', 'statusCode' => 200, 'data' => $data['data']);

        return $json->withJsonResponse($response, $payload);
    }

    /** */

    public function getOrganization(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getSelf($request, $response, new OrganizationModel());
    }

    public function logoutOrganization(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->logoutSelf($request, $response, new OrganizationModel());
    }
}
