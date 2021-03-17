<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\AdminFeatureUserModel;

class AdminFeatureUserController extends BaseController
{

    /** admin User */

    public function assignAdminFeature(Request $request, ResponseInterface $response): ResponseInterface
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
            new AdminFeatureUserModel(),
            [
                "required" => [
                    "admin_user_id", "admin_feature_id"
                ],

                "expected" => [
                    "admin_user_id", "admin_feature_id"
                ],
            ]
        );
    }

    public function getAssignedAdminFeatures(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return $json->withJsonResponse($response, $error);
        }
        
        $routeParams = $this->getRouteParams($request, ["admin_user_id", "admin_feature_id"]);
        $conditions = [];
        if (isset($routeParams["admin_user_id"]) && $routeParams["admin_user_id"] != "-") {
            $conditions["admin_user_id"] = $routeParams["admin_user_id"];
        }
        if (isset($routeParams["admin_feature_id"]) && $routeParams["admin_feature_id"] != "-") {
            $conditions["admin_feature_id"] = $routeParams["admin_feature_id"];
        }

        return (new BaseController)->getByPage($request, $response, new AdminFeatureUserModel(), null, $conditions);
    }

    public function updateAssignedAdminFeatureByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $authDetails = static::getTokenInputsFromRequest($request);

        $ownerPriviledges = isset($authDetails["admin_priviledges"]) ? json_decode($authDetails["admin_priviledges"]) : [];
        if (!in_array(Constants::PRIVILEDGE_ADMIN_ADMIN, $ownerPriviledges)) {
            $error = ["errorMessage" => "You do not have sufficient priveleges to perform this action", "statusCode" => 400];

            return (new JSON())->withJsonResponse($response, $error);
        }

        return (new BaseController)->updateByPK(
            $request,
            $response,
            new AdminFeatureUserModel(),
            [
                "required" => [
                    "admin_user_id", "admin_feature_id"
                ],

                "expected" => [
                    "admin_user_id", "admin_feature_id"
                ],
            ]
        );
    }
}
