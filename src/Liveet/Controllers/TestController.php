<?php

namespace Liveet\Controllers;

use Liveet\Models\AdminUserModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Liveet\Models\LocationModel;
use Rashtell\Domain\JSON;

class TestController extends HelperController
{
    
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
