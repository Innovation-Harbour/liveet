<?php

namespace BUS_LOCATOR\Controllers;

use BUS_LOCATOR\Domain\Constants;
use BUS_LOCATOR\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use BUS_LOCATOR\Models\AdminModel;
use BUS_LOCATOR\Models\OrganizationModel;

class AdminController extends BaseController
{
    //super admin exclusive
    public function createAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["priviledges"]) ? json_decode($authDetails["priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_CREATE_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", 'statusCode' => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->create($request, $response, new AdminModel(), ['username', 'password', 'name', 'phone', 'email', 'address', 'priviledges'], ['isAccount' => true, 'sendMail' => true, 'userType' => MailHandler::USER_TYPE_ADMIN]);
    }

    public function loginAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->login($request, $response, new AdminModel(), ['username', 'password']);
    }

    public function getAdminDashboard(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getDashboard($request, $response, new AdminModel());
    }

    //super admin exclusive
    public function getAllAdmins(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["priviledges"]) ? json_decode($authDetails["priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_GET_ANY_ADMIN, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getAll($request, $response, new AdminModel());
    }

    public function getAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getSelf($request, $response, new AdminModel());
    }

    //super admin exclusive
    public function getAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["priviledges"]) ? json_decode($authDetails["priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_GET_ANY_ADMIN, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }
        return (new BaseController)->getById($request, $response, new AdminModel());
    }

    public function updateAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->updateSelf($request, $response, new AdminModel(), ['id', 'mobile_number', 'name', 'gender', 'email',  'phone', 'address', 'gps']);
    }

    //super admin exclusive
    public function updateAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["priviledges"]) ? json_decode($authDetails["priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_UPDATE_ANY_ADMIN, $ownerPriviledges)) {

            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->updateById($request, $response, new AdminModel(), ['id', 'mobile_number', 'name', 'gender', 'email',  'phone', 'address', 'gps', 'priviledges']);
    }

    public function updateAdminPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->updatePassword($request, $response, new AdminModel());
    }

    //super admin exclusive
    public function resetAdminPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["priviledges"]) ? json_decode($authDetails["priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_RESET_PASSWORDS, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->resetPassword($request, $response, new AdminModel());
    }

    public function verifyAdminEmail(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->verifyEmail($request, $response, new AdminModel());
    }

    public function deleteAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->deleteSelf($request, $response, new AdminModel());
    }

    //super admin exclusive
    public function deleteAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["priviledges"]) ? json_decode($authDetails["priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_DELETE_ANY_ADMIN, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->deleteById($request, $response, new AdminModel());
    }

    public function logoutAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->logoutSelf($request, $response, new AdminModel());
    }

    //super admin exclusive
    public function logoutAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["priviledges"]) ? json_decode($authDetails["priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_LOGOUT_ANY_ADMIN, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->logoutById($request, $response, new AdminModel());
    }

    public function generateHash(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();
        $model = new AdminModel();
        $inputs = [];
        $options = ['isAccount' => true ];
        $override = [];

        ['data' => $data, 'error' => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, $inputs);
        if ($allInputs['error']) {
            return $json->withJsonResponse($response, $allInputs['error']);
        }

        $allInputs = $this->appendSecurity($allInputs, $options);

        $data = $allInputs["password"];

        $payload = ['successMessage' => 'Generated successfully', 'statusCode' => 200, 'data' => $data];

        return $json->withJsonResponse($response, $payload);
    }
}
