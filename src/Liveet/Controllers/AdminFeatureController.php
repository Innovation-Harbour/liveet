<?php

namespace Liveet\Controllers;

use Liveet\Domain\Constants;
use Liveet\Domain\MailHandler;
use Liveet\Models\AdminActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;
use Liveet\Models\AdminFeatureModel;

class AdminFeatureController extends HelperController
{

    /** Admin */

    public function createAdminFeature(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "created an admin feature"]);

        return $this->createSelf(
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
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["admin_feature_id", "admin_user_id"];
        $routeParams = $this->getRouteParams($request);
        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $whereHas = [];
        if (isset($conditions["admin_user_id"])) {
            $admin_user_id = $conditions["admin_user_id"];

            $whereHas["adminUsers"] = function ($query) use ($admin_user_id) {
                return $query->where("admin_user.admin_user_id", $admin_user_id);
            };

            unset($conditions["admin_user_id"]);
        }

        return $this->getByPage($request, $response, new AdminFeatureModel(), null, $conditions, ["adminUsers"], ["whereHas" => $whereHas]);
    }

    public function getAdminFeatureByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        return $this->getByPK($request, $response, new AdminFeatureModel(), null, ["adminUsers"]);
    }

    public function updateAdminFeatureByPK(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminAdminPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        (new AdminActivityLogModel())->createSelf(["admin_user_id" => $authDetails["admin_user_id"], "activity_log_desc" => "updated an admin feature"]);


        ["admin_feature_id" => $admin_feature_id] = $this->getRouteParams($request, ["admin_feature_id"]);

        return $this->updateByPK(
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
