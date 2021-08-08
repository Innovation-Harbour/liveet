<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Liveet\Models\AdminActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\AdminUserModel;
use Liveet\Models\EventModel;

class AdminUserController extends HelperController
{

    public function loginAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->login($request, $response, new AdminUserModel(), ["admin_username", "admin_password"], ["publicKeyKey" => "public_key", "passwordKey" => "admin_password"], [
            "dataOptions" => [
                "overrideKeys" => [
                    // "username" => "admin_username", "password" => "admin_password"
                ]
            ],
        ]);
    }

    public function createAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created admin user"]);

        $this->checkAdminAdminPermission($request, $response);

        return $this->createSelf(
            $request,
            $response,
            new AdminUserModel(),
            [
                "required" => [
                    "admin_username", "admin_password", "admin_fullname", "admin_email", "admin_priviledges"
                ],

                "expected" => [
                    "admin_username", "admin_password", "admin_fullname", "admin_email", "admin_priviledges", "admin_profile_picture", "admin_profile_pictureType", "public_key", "email_verification_token"
                ],
            ],
            [
                "dataOptions" => [
                    // "overrideKeys" => [
                    //     "username" => "admin_username", "password" => "admin_password", "fullname" => "admin_fullname", "email" => "admin_email", "priviledges" => "admin_priviledges", "profile_picture" => "admin_profile_picture"
                    // ]
                ],
                "securityOptions" => [
                    "hasPassword" => true, "hasPublicKey" => true, "passwordKey" => "admin_password", "publicKeyKey" => "public_key"
                ],
                "emailOptions" => [

                    [
                        "emailKey" => "admin_email", "nameKey" => "admin_fullname", "usertype" => MailHandler::USER_TYPE_ADMIN, "mailtype" => MailHandler::TEMPLATE_CONFIRM_EMAIL
                    ],
                ],
                "mediaOptions" => [
                    [
                        "mediaKey" => "admin_profile_picture", "folder" => "admins",
                        "clientOptions" => [
                            "containerName" => "liveet-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ]
        );
    }

    public function getAdminUsers(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminAdminPermission($request, $response);

        return $this->getByPage($request, $response, new AdminUserModel());
    }

    public function getAdminUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $this->checkAdminAdminPermission($request, $response);

        return $this->getByPK($request, $response, new AdminUserModel());
    }

    public function updateAdminUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated admin user"]);

        $this->checkAdminAdminPermission($request, $response);

        return $this->updateByPK(
            $request,
            $response,
            (new AdminUserModel()),
            [
                "required" => [
                    "admin_username", "admin_fullname", "admin_priviledges"
                ],

                "expected" => [
                    "admin_user_id", "admin_username", "admin_fullname", "admin_email", "admin_priviledges", "admin_profile_picture"
                ]
            ],
            [

                "mediaOptions" => [
                    [
                        "mediaKey" => "admin_profile_picture", "folder" => "admins",
                        "clientOptions" => [
                            "containerName" => "liveet-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ],
            [],
            [
                [
                    "detailsKey" => "admin_user_id", "columnName" => "admin_user_id", "errorText" =>
                    "Admin User Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "admin_username", "columnName" => "admin_username", "errorText" =>
                    "Admin username"
                ],
                [
                    "detailsKey" => "admin_email", "columnName" => "admin_email", "errorText" =>
                    "Admin email"
                ]
            ]

        );
    }

    public function logoutAdminUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "loggded out admin user"]);

        $this->checkAdminAdminPermission($request, $response);

        return $this->logoutByPK($request, $response, new AdminUserModel());
    }

    public function getAdminUserDashboard(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getSelfDashboard($request, $response, new AdminUserModel());
    }

    public function getAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getSelf($request, $response, new AdminUserModel());
    }

    public function updateAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated profile"]);

        return $this->updateSelf(
            $request,
            $response,
            new AdminUserModel(),
            [
                "required" => [
                    "admin_username", "admin_fullname"
                ],

                "expected" => [
                    "admin_username", "admin_fullname", "admin_profile_picture"
                ],
            ],
            [
                "mediaOptions" => [
                    [
                        "mediaKey" => "admin_profile_picture", "folder" => "admins",
                        "clientOptions" => [
                            "containerName" => "liveet-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ],
            [],
            [
                [
                    "detailsKey" => "admin_user_id", "columnName" => "admin_user_id", "errorText" =>
                    "Admin User Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "admin_username", "columnName" => "admin_username", "errorText" =>
                    "Admin username"
                ]
            ]
        );
    }

    public function updateAdminUserPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "changed password"]);

        return $this->updatePassword($request, $response, new AdminUserModel());
    }

    public function logoutAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "logged out"]);

        return $this->logoutSelf($request, $response, new AdminUserModel());
    }

    /**

    //super admin exclusive
    public function resetAdminUserPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_RESET_PASSWORDS, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient privelege to perform this action", "statusCode", "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        return $this->resetPassword($request, $response, new AdminUserModel());
    }

    public function deleteAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->deleteSelf($request, $response, new AdminUserModel());
    }

    //super admin exclusive
    public function deleteAdminUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_DELETE_ANY_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient privelege to perform this action", "statusCode", "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        return $this->deleteByPK($request, $response, new AdminUserModel());
    }

     **/

    public function generateHash(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();
        $model = new AdminUserModel();
        $inputs = [];
        $options = ["isAccount" => true];
        $override = [];

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $allInputs = $this->appendSecurity($allInputs, $options);

        $data = $allInputs["password"];

        $payload = ["successMessage" => "Generated successfully", "statusCode" => 200, "data" => $data];

        return $json->withJsonResponse($response, $payload);
    }
}
