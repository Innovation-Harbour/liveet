<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\AdminUserModel;

class AdminUserController extends BaseController
{

    public function loginAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->login($request, $response, new AdminUserModel(), ['admin_username', 'admin_password'], ["publicKeyKey" => "public_key", "passwordKey" => "admin_password"]);
    }

    public function createAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", 'statusCode' => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->createSelf(
            $request,
            $response,
            new AdminUserModel(),
            [
                "required" => [
                    'admin_username', 'admin_password', 'admin_fullname', 'admin_email', 'admin_priviledges'
                ],

                "expected" => [
                    'admin_username', 'admin_password', 'admin_fullname', 'admin_email', 'admin_priviledges', "admin_profile_picture"
                ],
            ],
            [
                "dataOptions" => [
                    "overrideKeys" => [
                        "admin_username" => "admin_username", "admin_password" => "admin_password", "admin_fullname" => "admin_fullname", "admin_email" => "admin_email", "admin_priviledges" => "admin_priviledges", "admin_profile_picture" => "admin_profile_picture"
                    ]
                ],
                "securityOptions" => [
                    "hasPassword" => true, "hasPublicKey" => true, "passwordKey" => "admin_password", "publicKeyKey" => "public_key"
                ],
                "emailOptions" => [

                    [
                        "emailKey" => "admin_email", 'nameKey' => "admin_fullname", 'usertype' => MailHandler::USER_TYPE_ADMIN, 'mailtype' => MailHandler::TEMPLATE_CONFIRM_EMAIL
                    ],
                ],
                "imageOptions" => [
                    [
                        "imageKey" => "admin_profile_picture"
                    ]
                ]
            ]
        );
    }

    public function getAllAdminUsers(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", 'statusCode' => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPage($request, $response, new AdminUserModel(), null, null, null, ["idKey" => "admin_user_id"]);
    }

    /**
    public function getAdminDashboard(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getDashboard($request, $response, new AdminUserModel());
    }

    public function getAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getSelf($request, $response, new AdminUserModel());
    }

    //super admin exclusive
    public function getAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_GET_ANY_ADMIN, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }
        return (new BaseController)->getById($request, $response, new AdminUserModel());
    }

    public function updateAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->updateSelf($request, $response, new AdminUserModel(), ['id', 'mobile_number', 'name', 'gender', 'email',  'phone', 'address', 'gps']);
    }

    //super admin exclusive
    public function updateAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_UPDATE_ANY_ADMIN, $ownerPriviledges)) {

            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->updateById($request, $response, new AdminUserModel(), ['id', 'mobile_number', 'name', 'gender', 'email',  'phone', 'address', 'gps', 'priviledges']);
    }

    public function updateAdminPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->updatePassword($request, $response, new AdminUserModel());
    }

    //super admin exclusive
    public function resetAdminPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_RESET_PASSWORDS, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->resetPassword($request, $response, new AdminUserModel());
    }

    public function verifyAdminEmail(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->verifyEmail($request, $response, new AdminUserModel());
    }

    public function deleteAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->deleteSelf($request, $response, new AdminUserModel());
    }

    //super admin exclusive
    public function deleteAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_DELETE_ANY_ADMIN, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->deleteById($request, $response, new AdminUserModel());
    }

    public function logoutAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->logoutSelf($request, $response, new AdminUserModel());
    }

    //super admin exclusive
    public function logoutAdminById(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_LOGOUT_ANY_ADMIN, $ownerPriviledges)) {
            $error = ['errorMessage' => "You do not have sufficient privelege to perform this action", "statusCode", 'errorStatus' => 1, 'statusCode' => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->logoutById($request, $response, new AdminUserModel());
    }

    public function generateHash(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();
        $model = new AdminUserModel();
        $inputs = [];
        $options = ['isAccount' => true];
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

     **/
}
