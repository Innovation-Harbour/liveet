<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Models\UserModel;
use Liveet\Models\OrganiserModel;
use Liveet\Models\EventModel;
use Liveet\Models\Mobile\TempsModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Aws\Rekognition\RekognitionClient;
use Aws\S3\S3Client;
use Rashtell\Domain\KeyManager;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrganiserController extends BaseController {
  use LiveetFunction;

  public function __construct (){
    $this->json = new JSON();
  }

  public function Login (Request $request, ResponseInterface $response): ResponseInterface
  {

    //declare needed class objects
    $organiser_db = new OrganiserModel();
    $data = $request->getParsedBody();

    $username = $data["username"];
    $password = $data["password"];

    $kmg = new KeyManager();
    $hashed_password = $kmg->getDigest($password);

    $user_count = $organiser_db->where('organiser_username', $username)->count();

    if($user_count < 1)
    {
      $error = ["errorMessage" => "Username Not Registered. Please Try Again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $organiser_data = $organiser_db->where('organiser_username', $username)->first();

    $db_password = $organiser_data->organiser_password;

    if($hashed_password !== $db_password)
    {
      $error = ["errorMessage" => "Password Not Correct. Please Try Again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    //get user data
    $fullname = $organiser_data->organiser_name;
    $organiser_id = $organiser_data->organiser_id;

    $data_to_view = ["name" => $fullname,"id" => $organiser_id];

    $payload = ["statusCode" => 200, "data" => $data_to_view];

    return $this->json->withJsonResponse($response, $payload);
  }

  public function getOrganiserEvent (Request $request, ResponseInterface $response, array $args): ResponseInterface
  {
    //declare needed class objects
    $db = new EventModel();


    $response_data = [];

    $organiser_id = $args["organiser_id"];
    $offset = $args["offset"];
    $limit = $args["limit"];

    $invited_for_results = $db->where("organiser_id", $organiser_id)->offset($offset)->limit($limit)->get();

    foreach($invited_for_results as $result)
    {
      $datetime = $result->event_date_time;
      $date = date('d',$datetime);
      $month = date('M',$datetime);
      $month_num = date('n',$datetime);
      $year = date('Y',$datetime);

      $eventdate_formatted = date('d-n-Y',$datetime);

      $now_formatted = date('d-n-Y');

      if($eventdate_formatted === $now_formatted)
      {
        $tmp = [
          "event_id" => intval($result->event_id),
          "event_image" => $result->event_multimedia,
          "event_title" => $result->event_name,
          "event_date" => intval($date),
          "event_month" => $month,
          "event_year" => $year,
        ];

        array_push($response_data,$tmp);
      }
    }

    $payload = ["statusCode" => 200, "data" => $response_data];

    return $this->json->withJsonResponse($response, $payload);
  }

  public function verifyUser (Request $request, ResponseInterface $response, array $args): ResponseInterface
  {
    //declare needed class objects
    $db = new EventModel();
    $temp_db = new TempsModel();


    $response_data = [];

    $event_id = $args["event_id"];

    $data = $request->getParsedBody();

    $image = $data["image"];

    $temp_db->create(["base_64" => $image]);

    $response_data = [];

    $payload = ["statusCode" => 200, "data" => $response_data];

    return $this->json->withJsonResponse($response, $payload);
  }

}
