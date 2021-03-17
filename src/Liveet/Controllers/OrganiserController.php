<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\OrganiserModel;
use Liveet\Models\OrganiserStaffModel;

class OrganiserController extends BaseController
{

    /** Admin User */

    public function createOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->createSelf(
            $request,
            $response,
            new OrganiserModel(),
            [
                "required" => [
                    "organiser_name", "organiser_email", "organiser_phone", "organiser_address", "organiser_username", "organiser_password"
                ],

                "expected" => [
                    "organiser_name", "organiser_email", "organiser_phone", "organiser_address", "organiser_username", "organiser_password", "organiser_profile_picture", "email_verification_token", "public_key"
                ],
            ],
            [
                "securityOptions" => [
                    "hasPassword" => true, "hasPublicKey" => true, "passwordKey" => "organiser_password", "publicKeyKey" => "public_key"
                ],
                "emailOptions" => [
                    [
                        "emailKey" => "organiser_email", "nameKey" => "organiser_name", "usertype" => MailHandler::USER_TYPE_ORGANISER, "mailtype" => MailHandler::TEMPLATE_CONFIRM_EMAIL
                    ],
                    [
                        "emailKey" => "organiser_email", "nameKey" => "organiser_name", "usertype" => MailHandler::USERTYPE_ORGANISER_STAFF, "mailtype" => MailHandler::TEMPLATE_CONFIRM_EMAIL
                    ],
                ],
                "imageOptions" => [
                    [
                        "imageKey" => "organiser_profile_picture"
                    ]
                ]
            ]
        );
    }

    public function getOrganisers(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPage($request, $response, new OrganiserModel(), null, ["usertype" => Constants::USERTYPE_ORGANISER_ADMIN]);
    }

    public function getOrganiserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPK($request, $response, new OrganiserModel());
    }

    public function updateOrganiserByPK(Request $request, ResponseInterface $response): ResponseInterface
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
            (new OrganiserModel()),
            [
                "required" => [
                    "organiser_username", "organiser_name", "organiser_phone"
                ],

                "expected" => [
                    "organiser_id", "organiser_username", "organiser_name", "organiser_phone", "organiser_address", "organiser_profile_picture"
                ]
            ],
            [],
            [],
            [
                [
                    "detailsKey" => "organiser_id", "columnName" => "organiser_id", "errorText" =>
                    "Organiser User Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "organiser_username", "columnName" => "organiser_username", "errorText" =>
                    "Organiser username"
                ]
            ]

        );
    }

    public function logoutOrganiserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->logoutByPK($request, $response, new OrganiserModel());
    }

    /**
     * Disable Organiser

    public function deleteOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->deleteSelf($request, $response, new OrganiserModel());
    }

    public function deleteOrganiserByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["organiser_priviledges"]) ? json_decode($authDetails["organiser_priviledges"]) : [];

        if (!in_array(Constants::PRIVILEDGE_DELETE_ANY_ORGANISER, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient privelege to perform this action", "statusCode", "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->deleteByPK($request, $response, new OrganiserModel());
    }

     **/


    /** Organiser Admin */

    public function loginOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->login($request, $response, new OrganiserModel(), ["organiser_username", "organiser_password"], ["publicKeyKey" => "public_key", "passwordKey" => "organiser_password"]);
    }

    public function updateOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $usertype = $authDetails["usertype"];
        if ($usertype != Constants::USERTYPE_ORGANISER_ADMIN) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }
        $organiser_id = $authDetails["organiser_id"];

        return (new BaseController)->updateByConditions(
            $request,
            $response,
            new OrganiserModel(),
            [
                "required" => [
                    "organiser_name", "organiser_phone", "organiser_username"
                ],

                "expected" => [
                    "organiser_name",  "organiser_phone", "organiser_address", "organiser_username", "organiser_profile_picture"
                ],
            ],
            ["organiser_id" => $organiser_id],
            [
                [
                    "detailsKey" => "organiser_id", "columnName" => "organiser_id", "errorText" =>
                    "Organiser Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "organiser_username", "columnName" => "organiser_username", "errorText" =>
                    "Organiser username"
                ],
                [
                    "detailsKey" => "organiser_phone", "columnName" => "organiser_phone", "errorText" =>
                    "Organiser phone"
                ]
            ],
            ["organiser_id" => $organiser_id],
        );
    }

    public function logoutOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController)->logoutSelf($request, $response, new OrganiserModel());
    }

    /** Organiser Admin and Staff */

    public function getOrganiser(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        return (new BaseController)->getByConditions($request, $response, new OrganiserModel(), ["organiser_id" => $organiser_id]);
    }
}
