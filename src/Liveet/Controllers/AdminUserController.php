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

    /** 755 */

    public function loginAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->loginSelf($request, $response, new AdminUserModel(), ["admin_username", "admin_password"], ["publicKeyKey" => "public_key", "passwordKey" => "admin_password"], [
            "dataOptions" => [
                "overrideKeys" => [
                    // "username" => "admin_username", "password" => "admin_password"
                ]
            ],
        ]);
    }

    /** Self */

    public function getAdminUserDashboard(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getSelfDashboard($request, $response, new AdminUserModel());
    }

    public function getAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getSelf($request, $response, new AdminUserModel(), null, ["adminFeatures"]);
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
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
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

    public function disableAdminAccessStatus(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);
        $adminID = $authDetails["admin_user_id"];

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $adminID, "activity_log_desc" => "disabled account"]);

        return $this->updateByConditions($request, $response, new AdminUserModel(), [], ["admin_user_id" => $adminID], [], ["accessStatus" => Constants::USER_DISABLED], ["responseMessage" => "Admin account was disabled successfully"]);
    }

    public function logoutAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "logged out"]);

        return $this->logoutSelf($request, $response, new AdminUserModel());
    }

    /** Others */

    public function createAdminUser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created admin user"]);

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
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ]
        );
    }

    public function getAdminUsers(Request $request, ResponseInterface $response): ResponseInterface
    {

        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPage($request, $response, new AdminUserModel(), null, null, ["adminFeatures"]);
    }

    public function getAdminUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, new AdminUserModel(), null, ["adminFeatures"]);
    }

    public function updateAdminUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated admin user"]);

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
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
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

    public function resetAdminUserPasswordByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "resetted an admin password"]);

        return $this->resetPassword($request, $response, new AdminUserModel(), [], ["passwordKey" => "admin_password", "publicKeyKey" => "public_key"]);
    }

    public function logoutAdminUserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "loggded out admin user"]);

        return $this->logoutByPK($request, $response, new AdminUserModel());
    }

    public function toggleAdminUserAccessStatusByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->toggleUserAccessStatusByPK($request, $response, new AdminUserModel());
    }


}
