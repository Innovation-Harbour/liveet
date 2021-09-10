<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Liveet\Models\AdminActivityLogModel;
use Liveet\Models\OrganiserActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\OrganiserStaffModel;

class OrganiserStaffController extends HelperController
{

    /** Admin User */

    public function getOrganiserStaffs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["organiser_staff_id", "organiser_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new OrganiserStaffModel(), null, $conditions);
    }

    public function getOrganiserStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, new OrganiserStaffModel());
    }

    public function logoutOrganiserStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "logged out an organiser staff"]);

        return $this->logoutByPK($request, $response, new OrganiserStaffModel());
    }

    public function toggleOrganiserStaffAccessStatusByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->toggleUserAccessStatusByPK($request, $response, new OrganiserStaffModel());
    }

    /**   */

    public function createOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created an organiser"]);

        return (new OrganiserController())->createOrganiser($request, $response);
    }

    /** */

    public function updateOrganiserStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an organiser staff details"]);

        return $this->updateByPK(
            $request,
            $response,
            (new OrganiserStaffModel()),
            [
                "required" => [
                    "organiser_name", "organiser_phone", "organiser_staff_priviledges"
                ],

                "expected" => [
                    "organiser_id", "organiser_name", "organiser_email", "organiser_staff_priviledges", "organiser_profile_picture"
                ]
            ],
            [

                "mediaOptions" => [
                    [
                        "mediaKey" => "organiser_profile_picture", "folder" => "organiser-staffs",
                        "clientOptions" => [
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ],
            [],
            [
                [
                    "detailsKey" => "organiser_id", "columnName" => "organiser_id", "errorText" =>
                    "Organiser Staff Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "organiser_username", "columnName" => "organiser_username", "errorText" =>
                    "Organiser Staff username"
                ],
                [
                    "detailsKey" => "organiser_email", "columnName" => "organiser_email", "errorText" =>
                    "Organiser Staff email"
                ]
            ]

        );
    }

    /** Organiser Admin */

    public function createOrganiserSelfStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "created an organiser staff"]);

        $organiser_id = $authDetails["organiser_id"];

        return $this->createSelf(
            $request,
            $response,
            new OrganiserStaffModel(),
            [
                "required" => [
                    "organiser_staff_name", "organiser_staff_username", "organiser_staff_password", "organiser_staff_email"
                ],

                "expected" => [
                    "organiser_staff_name", "organiser_staff_username", "organiser_staff_password", "organiser_staff_phone", "organiser_staff_email", "organiser_staff_profile_picture", "organiser_staff_priviledges", "organiser_id", "public_key", "email_verification_token"
                ],
            ],
            [
                "securityOptions" => [
                    "hasPassword" => true, "hasPublicKey" => true, "passwordKey" => "organiser_staff_password", "publicKeyKey" => "public_key"
                ],
                "emailOptions" => [
                    [
                        "emailKey" => "organiser_staff_email", "nameKey" => "organiser_staff_name", "usertype" => MailHandler::USERTYPE_ORGANISER_STAFF, "mailtype" => MailHandler::TEMPLATE_CONFIRM_EMAIL
                    ],
                ],
                "mediaOptions" => [
                    [
                        "mediaKey" => "organiser_staff_profile_picture", "folder" => "organiser-staffs",
                        "clientOptions" => [
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ],
            [
                "usertype" => Constants::USERTYPE_ORGANISER_STAFF,
                "organiser_id" => $organiser_id
            ],
            [
                [
                    "detailsKey" => "organiser_staff_username", "columnName" => "organiser_staff_username", "errorText" =>
                    "Organiser Staff Username"
                ],
                [
                    "detailsKey" => "organiser_staff_email", "columnName" => "organiser_staff_email", "errorText" =>
                    "Organiser Staff Email"
                ]
            ],
        );
    }

    public function getOrganiserSelfStaffs(Request $request, ResponseInterface $response): ResponseInterface
    {   
        $permissonResponse = $this->checkOrganiserOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["organiser_staff_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = ["organiser_id" => $organiser_id];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByConditions($request, $response, new OrganiserStaffModel(), $conditions);
    }

    public function getOrganiserSelfStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_id = $authDetails["organiser_id"];
        ["organiser_staff_id" => $organiser_staff_id] = $this->getRouteParams($request, ["organiser_staff_id"]);

        return $this->getByConditions($request, $response, new OrganiserStaffModel(), ["organiser_staff_id" => $organiser_staff_id, "organiser_id" => $organiser_id]);
    }

    public function updateOrganiserSelfStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "updated an organiser staff details"]);

        $organiser_id = $authDetails["organiser_id"];
        ["organiser_staff_id" => $organiser_staff_id] = $this->getRouteParams($request, ["organiser_staff_id"]);

        return $this->updateByConditions(
            $request,
            $response,
            (new OrganiserStaffModel()),
            [
                "required" => [
                    "organiser_staff_name", "organiser_staff_username"
                ],

                "expected" => [
                    "organiser_staff_name", "organiser_staff_username", "organiser_staff_phone", "organiser_staff_profile_picture", "organiser_staff_priviledges"
                ],
            ],
            ["organiser_staff_id" => $organiser_staff_id, "organiser_id" => $organiser_id],
            [
                [
                    "detailsKey" => "organiser_staff_id", "columnName" => "organiser_staff_id", "errorText" =>
                    "Organiser Staff Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "organiser_staff_username", "columnName" => "organiser_staff_username", "errorText" =>
                    "Organiser Staff Username"
                ]
            ],
            ["organiser_staff_id" => $organiser_staff_id],
            [
                "mediaOptions" => [
                    [
                        "mediaKey" => "organiser_staff_profile_picture", "folder" => "organiser-staffs",
                        "clientOptions" => [
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ],
            ["useParentModel" => true]

        );
    }

    public function logoutOrganiserSelfStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "logged out an organiser staff"]);

        $organiser_id = $authDetails["organiser_id"];
        ["organiser_staff_id" => $organiser_staff_id] = $this->getRouteParams($request, ["organiser_staff_id"]);

        return $this->logoutByCondition($request, $response, new OrganiserStaffModel(), ["organiser_staff_id" => $organiser_staff_id, "organiser_id" => $organiser_id]);
    }

    public function updateOrganiserAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "Updated organiser admin details"]);
        $organiser_id = $authDetails["organiser_id"];
        $organiser_staff_id = $authDetails["organiser_staff_id"];

        return $this->updateByConditions(
            $request,
            $response,
            new OrganiserStaffModel(),
            [
                "required" => [
                    "organiser_staff_name", "organiser_staff_phone", "organiser_staff_username"
                ],

                "expected" => [
                    "organiser_staff_name",  "organiser_staff_phone", "organiser_staff_address", "organiser_staff_username", "organiser_staff_profile_picture"
                ],
            ],
            ["organiser_id" => $organiser_id],
            [
                [
                    "detailsKey" => "organiser_staff_id", "columnName" => "organiser_staff_id", "errorText" =>
                    "Organiser Staff Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "organiser_staff_username", "columnName" => "organiser_staff_username", "errorText" =>
                    "Organiser Staff Username"
                ],
                [
                    "detailsKey" => "organiser_staff_phone", "columnName" => "organiser_staff_phone", "errorText" =>
                    "Organiser Staff Phone"
                ]
            ],
            ["organiser_id" => $organiser_id, "organiser_staff_id" => $organiser_staff_id],
            [
                "mediaOptions" => [
                    [
                        "mediaKey" => "organiser_staff_profile_picture", "folder" => "organiser-staffs",
                        "clientOptions" => [
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ]
        );
    }

    public function toggleOrganiserSelfStaffAccessStatusByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminOrganiserPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        return $this->toggleUserAccessStatusByPK($request, $response, new OrganiserStaffModel(), null, ["organiser_id" => $organiser_id]);
    }

    /** Organiser Admin or Staff */

    public function loginOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->loginSelf(
            $request,
            $response,
            new OrganiserStaffModel(),
            ["organiser_staff_username", "organiser_staff_password"],
            ["publicKeyKey" => "public_key", "passwordKey" => "organiser_staff_password"],
            [
                // "dataOptions" => [
                //     "overrideKeys" => [
                //         "username" => "organiser_staff_username", "password" => "organiser_staff_password"
                //     ]
                // ],
            ]
        );
    }

    public function getOrganiserAdminOrStaffDashboard(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getSelfDashboard($request, $response, new OrganiserStaffModel());
    }

    public function getOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getSelf($request, $response, new OrganiserStaffModel());
    }

    public function updateOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "Updated organiser staff details"]);

        $organiser_id = $authDetails["organiser_id"];
        $usertype = $authDetails["usertype"];

        if ($usertype == Constants::USERTYPE_ORGANISER_ADMIN) {
            return $this->updateOrganiserAdmin($request, $response);
        }

        return $this->updateSelf(
            $request,
            $response,
            new OrganiserStaffModel(),
            [
                "required" => [
                    "organiser_staff_username", "organiser_staff_name", "organiser_staff_phone"
                ],

                "expected" => [
                    "organiser_staff_username", "organiser_staff_name",  "organiser_staff_phone", "organiser_staff_profile_picture"
                ],
            ],
            [
                "mediaOptions" => [
                    [
                        "mediaKey" => "organiser_staff_profile_picture", "folder" => "organiser-staffs",
                        "clientOptions" => [
                            "containerName" => "liveet-prod-media", "mediaName" => rand(00000000, 99999999)
                        ]
                    ]
                ]
            ],
            ["organiser_id" => $organiser_id],
            [
                [
                    "detailsKey" => "organiser_staff_id", "columnName" => "organiser_staff_id", "errorText" =>
                    "Organiser Staff Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "organiser_staff_username", "columnName" => "organiser_staff_username", "errorText" =>
                    "Organiser Staff Username"
                ]
            ]
        );
    }

    public function updateOrganiserAdminOrStaffPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "updated organiser staff password"]);

        return $this->updatePassword($request, $response, new OrganiserStaffModel());
    }

    public function logoutOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $organiser_staff_id = isset($authDetails["organiser_staff_id"]) ? $authDetails["organiser_staff_id"] : OrganiserStaffModel::where("organiser_id", $authDetails["organiser_id"])->first()["organiser_staff_id"];

        (new OrganiserActivityLogModel())->createSelf(["organiser_staff_id" => $organiser_staff_id, "activity_log_desc" => "organiser staff logged out"]);

        return $this->logoutSelf($request, $response, new OrganiserStaffModel());
    }
}
