<?php

namespace Liveet\Controllers;

use Liveet\Models\UserModel;
use Liveet\Models\ActivityLogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends HelperController
{

    /** Admin User */

    public function getUsers(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->checkAdminUserPermission($request, $response);

        $expectedRouteParams = ["user_id", "user_phone", "fcm_token"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getByPage($request, $response, new UserModel(), null, $conditions);
    }
}
