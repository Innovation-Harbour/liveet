<?php

namespace Liveet\Controllers;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\EventModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends BaseController {

  public function Register (Request $request, ResponseInterface $response): ResponseInterface
  {
    $json = new JSON();

    $data = $request->getParsedBody();

    $phone = $data["phone"];

    $country_code = substr($phone, 0, 4);

    $rest_of_phone_number = substr($phone, 4);

    $phone_count = strlen($rest_of_phone_number);
    
    if(($phone_count == 11 && $rest_of_phone_number[0] == "0") || $phone_count == 10)
    {
      $data_to_view = ["country_code" => $country_code, "Phone Number" => $rest_of_phone_number, "Count" => $phone_count];

      $payload = ["statusCode" => 200, "data" => $data_to_view];

      return $json->withJsonResponse($response, $payload);
    }
    else{
      $error = ["errorMessage" => "Phone Number Does Not Match The Number Format for Selected Country", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }


  }

}
