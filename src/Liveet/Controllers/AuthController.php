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

    //die(var_dump($data));

    $phone = $data["phone"];

    $payload = ["statusCode" => 200, "data" => $phone];

    return $json->withJsonResponse($response, $payload);
  }

}
