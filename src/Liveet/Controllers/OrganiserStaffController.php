<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\OrganiserStaffModel;

class OrganiserStaffController extends BaseController
{

    /** Organiser Admin or Staff */

    public function loginOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->login(
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
        return (new BaseController)->getSelfDashboard($request, $response, new OrganiserStaffModel());
    }

    public function getOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->getSelf($request, $response, new OrganiserStaffModel());
    }

    public function updateOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];
        $usertype = $authDetails["usertype"];

        if ($usertype == Constants::USERTYPE_ORGANISER_ADMIN) {
            return $this->updateOrganiserAdmin($request, $response);
        }

        return (new BaseController)->updateSelf(
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
                "imageOptions" => [
                    [
                        "imageKey" => "organiser_staff_profile_picture"
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

    public function updateOrganiserAdmin(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $usertype = $authDetails["usertype"];
        if ($usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];
        $organiser_staff_id = $authDetails["organiser_staff_id"];

        return (new BaseController)->updateByConditions(
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
                "imageOptions" => [
                    [
                        "imageKey" => "organiser_staff_profile_picture"
                    ]
                ]
            ]
        );
    }

    public function updateOrganiserAdminOrStaffPassword(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->updatePassword($request, $response, new OrganiserStaffModel());
    }

    public function logoutOrganiserAdminOrStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->logoutSelf($request, $response, new OrganiserStaffModel());
    }

    /** Organiser Admin */

    public function createOrganiserSelfStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_staff_priviledges"]) && gettype($authDetails["organiser_staff_priviledges"]) == "array" ? json_decode($authDetails["organiser_staff_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_ORGANISER, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];

        return (new BaseController)->createSelf(
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
                "imageOptions" => [
                    [
                        "imageKey" => "organiser_staff_profile_picture"
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
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_staff_priviledges"]) && gettype($authDetails["organiser_staff_priviledges"]) == "array" ? json_decode($authDetails["organiser_staff_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_ORGANISER, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];

        return (new BaseController)->getByConditions($request, $response, new OrganiserStaffModel(), ["organiser_id" => $organiser_id]);
    }

    public function getOrganiserSelfStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_staff_priviledges"]) && gettype($authDetails["organiser_staff_priviledges"]) == "array" ? json_decode($authDetails["organiser_staff_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_ORGANISER, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];
        ["organiser_staff_id" => $organiser_staff_id] = $this->getRouteParams($request, ["organiser_staff_id"]);

        return (new BaseController)->getByConditions($request, $response, new OrganiserStaffModel(), ["organiser_staff_id" => $organiser_staff_id, "organiser_id" => $organiser_id]);
    }

    public function updateOrganiserSelfStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_staff_priviledges"]) && gettype($authDetails["organiser_staff_priviledges"]) == "array" ? json_decode($authDetails["organiser_staff_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_ORGANISER, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];
        ["organiser_staff_id" => $organiser_staff_id] = $this->getRouteParams($request, ["organiser_staff_id"]);

        return (new BaseController)->updateByConditions(
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
                "imageOptions" => [
                    [
                        "imageKey" => "organiser_staff_profile_picture"
                    ]
                ]
            ],
            ["useParentModel" => true]

        );
    }

    public function logoutOrganiserSelfStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_staff_priviledges"]) && gettype($authDetails["organiser_staff_priviledges"]) == "array" ? json_decode($authDetails["organiser_staff_priviledges"]) : [];
        $usertype = $authDetails["usertype"];
        if (!in_array(Constants::PRIVILEDGE_ORGANISER_ORGANISER, $ownerPriviledges) && $usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];
        ["organiser_staff_id" => $organiser_staff_id] = $this->getRouteParams($request, ["organiser_staff_id"]);

        return (new BaseController)->logoutByCondition($request, $response, new OrganiserStaffModel(), ["organiser_staff_id" => $organiser_staff_id, "organiser_id" => $organiser_id]);
    }

    /** Admin User */

    public function getOrganiserStaffs(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPage($request, $response, new OrganiserStaffModel());
    }

    public function getOrganiserStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPK($request, $response, new OrganiserStaffModel());
    }

    public function logoutOrganiserStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->logoutByPK($request, $response, new OrganiserStaffModel());
    }

    /**
     * Disable Organiser

    public function deleteOrganiserStaff(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->deleteSelf($request, $response, new OrganiserStaffModel());
    }

    public function deleteOrganiserStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_staff_priviledges"]) ? json_decode($authDetails["organiser_staff_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_DELETE_ANY_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient privelege to perform this action", "statusCode", "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->deleteByPK($request, $response, new OrganiserStaffModel());
    }

     **/

    public function createOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new OrganiserController())->createOrganiser($request, $response);
    }

    public function updateOrganiserStaffByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->updateByPK(
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

                "imageOptions" => [
                    [
                        "imageKey" => "organiser_profile_picture"
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
}
