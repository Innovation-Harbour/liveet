<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Models\AdminActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\AdminFeatureUserModel;

class AdminFeatureUserController extends HelperController
{

    /** admin User */

    public function assignAdminFeature(Request $request, ResponseInterface $response): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "assigned an admin feature to a user"]);

        $this->checkAdminAdminPermission($request, $response);

        return $this->createSelf(
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

        $this->checkAdminAdminPermission($request, $response);

        $routeParams = $this->getRouteParams($request, ["admin_user_id", "admin_feature_id"]);
        $conditions = [];
        if (isset($routeParams["admin_user_id"]) && $routeParams["admin_user_id"] != "-") {
            $conditions["admin_user_id"] = $routeParams["admin_user_id"];
        }
        if (isset($routeParams["admin_feature_id"]) && $routeParams["admin_feature_id"] != "-") {
            $conditions["admin_feature_id"] = $routeParams["admin_feature_id"];
        }

        return $this->getByPage($request, $response, new AdminFeatureUserModel(), null, $conditions);
    }

    public function updateAssignedAdminFeatureByPK(Request $request, ResponseInterface $response): ResponseInterface
    {

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an assigned admin user feature"]);

        $this->checkAdminAdminPermission($request, $response);

        return $this->updateByPK(
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
