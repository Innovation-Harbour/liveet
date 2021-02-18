<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Models\BaseModel;
use DateTime;

class BaseController
{

    protected function getValidJsonOrError($request)
    {
        $json = new JSON();

        $data = $request->getParsedBody();
        $data = isset($data) ? $data : $request->getBody();


        $validJson = $json->jsonFormat($data);

        if ($validJson == NULL) {
            $error = array('errorMessage' => 'The parameter is not a valid objects', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $error, 'data' => null];
        }

        if (!isset($validJson->data)) {
            $error = array('errorMessage' => 'The request object does not conform to standard', 'errorStatus' => 1, 'statusCode' => 400);

            return ['error' => $error, 'data' => null];
        }

        return ['data' => $validJson->data, 'error' => ""];
    }

    protected function getPageNumOrError($request)
    {
        $data = $request->getAttributes();
        $page = 1;

        if (!(isset($data['page']))) {
            // $error = array('errorMessage' => 'Page is required', 'errorStatus' => 1, 'statusCode' => 400);

            // return ['error' => $error, 'page' => null];
            $page = 1;
        } else {
            $page = $data['page'];
        }


        if (!(is_numeric($page) || (int) $page < 0)) {
            // $error = array('errorMessage' => 'The page number is invalid', 'errorStatus' => 1, 'statusCode' => 400);

            // return ['error' => $error, 'page' => null];
            $page = 1;
        }

        return ['page' => $page, 'error' => null];
    }

    protected function getRouteParams($request, $details)
    {
        $data = $request->getAttributes();

        $existData = ['error' => null];

        foreach ($details as $detail) {
            if (!isset($data[$detail])) {

                $error = array('errorMessage' => 'Invalid request: ' . $detail . " not set", 'errorStatus' => 1, 'statusCode' => 400);

                $existData = array_merge($existData, ['error' => $error]);
                return $existData;
            }

            $existData = array_merge($existData, [$detail => $data[$detail]]);
        }

        return $existData;

        // return $request->getAttributes();
    }

    protected function getRouteTokenOrError($request)
    {
        if (!isset($request->getAttributes()['token'])) {
            $error = array('errorMessage' => 'Invalid url', 'errorStatus' => 1, 'statusCode' => 400);
            return ["error" => $error, "token" => ""];
        }

        $token = $request->getAttributes()['token'];

        return ["data" => $token, "error" => ""];
    }

    protected function valuesExistsOrError($data, array $details = [])
    {
        $existData = ['error' => null];

        foreach ($details as $detail) {
            if (!isset($data->$detail)) {
                $json = new JSON();

                $error = array('errorMessage' => 'All fields are required: ' . $detail . " not set", 'errorStatus' => 1, 'statusCode' => 400);

                $existData = array_merge($existData, ['error' => $error, 'username' => null, 'password' => null]);
                return $existData;
            }

            $existData = array_merge($existData, [$detail => $data->$detail]);
        }

        foreach ($data as $key => $value) {
            $existData[$key] = $value;
        }

        return $existData;
    }

    public static function getTokenInputsFromRequest($request)
    {
        $token = static::getToken($request);

        if (!$token) {
            return [];
        }

        $authDetails = (new BaseModel)->getTokenInputs($token);

        return $authDetails;
    }

    public static function getToken($request)
    {
        $headers = $request->getHeaders();

        $authorization = isset($headers['Token']) ? $headers['Token'] : (isset($headers['token']) ? $headers['token'] : null);

        if (!$authorization) {
            return null;
        }

        $token = $authorization[0];

        $token = explode(' ', $token)[1];

        return $token;
    }
}
