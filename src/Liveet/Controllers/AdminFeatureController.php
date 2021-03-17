<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\AdminFeatureModel;

class AdminFeatureController extends BaseController
{

    /** admin User */

    public function createAdminFeature(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->createSelf(
            $request,
            $response,
            new AdminFeatureModel(),
            [
                "required" => [
                    "feature_name", "feature_url"
                ],

                "expected" => [
                    "feature_name", "feature_url"
                ],
            ],
            [],
            [],
            [
                [
                    "detailsKey" => "feature_name", "columnName" => "feature_name", "errorText" =>
                    "Feature Name"
                ],
            ]
        );
    }

    public function getAdminFeatures(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPage($request, $response, new AdminFeatureModel());
    }

    public function getAdminFeatureByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }

        return (new BaseController)->getByPK($request, $response, new AdminFeatureModel());
    }

    public function updateAdminFeatureByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }

        ["admin_feature_id" => $admin_feature_id] = $this->getRouteParams($request, ["admin_feature_id"]);

        return (new BaseController)->updateByPK(
            $request,
            $response,
            new AdminFeatureModel(),
            [
                "required" => [
                    "feature_name", "feature_url"
                ],

                "expected" => [
                    "feature_name",  "feature_url", "admin_feature_id"
                ],
            ],
            ["admin_feature_id" => $admin_feature_id],
            [],
            [
                [
                    "detailsKey" => "admin_feature_id", "columnName" => "admin_feature_id", "errorText" =>
                    "Admin Feature Id", "primaryKey" => true
                ],
                [
                    "detailsKey" => "feature_name", "columnName" => "feature_name", "errorText" =>
                    "Feature Name"
                ],
            ]
        );
    }
}
