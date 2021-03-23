<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\Mobile\TempModel;
use Liveet\Models\UserModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends BaseController {

  public function Register (Request $request, ResponseInterface $response): ResponseInterface
  {
    $eligible_phone_starting = array("6","7","8","9");

    //declare needed class objects
    $json = new JSON();
    $user_db = new UserModel();
    $temp_db = new TempModel();

    $data = $request->getParsedBody();

    $phone = $data["phone"];

    $country_code = substr($phone, 0, 4);

    $rest_of_phone_number = substr($phone, 4);

    $phone_count = strlen($rest_of_phone_number);

    if ($country_code !=="+234")
    {
      $error = ["errorMessage" => "Selected Country not supported at the moment for now", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }

    if($phone_count == 10 && in_array($rest_of_phone_number[0], $eligible_phone_starting))
    {
      $phone_clean = substr($phone, 1);

      $user_count = $user_db->where('user_phone', $phone_clean)->count();

      if($user_count > 0)
      {
        $error = ["errorMessage" => "Phone Number Already Registered", "statusCode" => 400];

        return $json->withJsonResponse($response, $error);
      }
      else{
        $temp_db->create(["temp_phone" => $phone_clean]);
      }

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
