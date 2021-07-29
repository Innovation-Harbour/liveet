<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Models\UserModel;
use Liveet\Models\OrganiserModel;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\EventTicketUserModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Aws\Rekognition\RekognitionClient;
use Aws\S3\S3Client;
use Rashtell\Domain\KeyManager;
use Bluerhinos\phpMQTT;
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

    $email = $data["email"];
    $password = $data["password"];

    $kmg = new KeyManager();
    $hashed_password = $kmg->getDigest($password);

    $user_count = $organiser_db->where('organiser_email', $email)->count();

    if($user_count < 1)
    {
      $error = ["errorMessage" => "Email Not Registered. Please Try Again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $organiser_data = $organiser_db->where('organiser_email', $email)->first();

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
    $event_db = new EventModel();
    $user_db = new UserModel();

    $event_id = $args["event_id"];

    $data = $request->getParsedBody();

    $image = $data["image"];

    [$is_approved,$ticketname,$user_id] = $this->checkFaceMatchForEvent($image,$event_id);

    if($is_approved && $ticketname && $user_id)
    {
      $user_details = $user_db->where("user_id",$user_id)->first();
      $fullname = $user_details->user_fullname;
      $phone = "+".$user_details->user_phone;

      $response_data = [
        "ticket_name" => $ticketname,
        "fullname" => $fullname,
        "phone" => $phone
      ];

      $payload = ["statusCode" => 200, "data" => $response_data];
      return $this->json->withJsonResponse($response, $payload);
    }
    else {
      $error = ["errorMessage" => "Face Not allowed access for this event", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }
  }

  public function turnstileVerifyUser (Request $request, ResponseInterface $response): ResponseInterface
  {
    //declare needed class objects
    $event_db = new EventModel();
    $user_db = new UserModel();

    $data = $request->getParsedBody();

    $image = $data["image"];
    $turnstile_id = $data["id"];

    $exploded_image = explode(",",$image);


    [$is_approved,$ticketname,$user_id] = $this->checkFaceMatchForEvent($exploded_image[1],$turnstile_id,true);

    $server = $_ENV["MQTT_SERVER"];
    $port = $_ENV["MQTT_PORT"];
    $username = $_ENV["MQTT_USER"];
    $password = $_ENV["MQTT_PASSWORD"];
    $client_id = 'liveet_mqtt_subscriber_2';

    $mqtt = new phpMQTT($server, $port, $client_id);

    $topic = 'mqtt/face/'.$turnstile_id;

    if($is_approved && $ticketname && $user_id)
    {
      $message = [
        "operator" => "Unlock",
        "messageId" => time(),
        "info" => [
          "facesluiceId" => $turnstile_id,
          "openDoor" => 1,
          "showInfo" => "Verified",
          "result" => "ok"
        ]
      ];
    }
    else {
      $message = [
        "operator" => "Unlock",
        "messageId" => time(),
        "info" => [
          "facesluiceId" => $turnstile_id,
          "openDoor" => 0,
          "showInfo" => "Not Verified",
          "result" => "ok"
        ]
      ];
    }

    $message = json_encode($message);

    if ($mqtt->connect(true, NULL, $username, $password)) {
      $mqtt->publish($topic,$message, 0, false);
      $mqtt->close();
    } else {
      var_dump("error sending MQTT");
      die;
    }

    $payload = ["statusCode" => 200, "successMessage" => "MQTT publish Successfully"];
    return $this->json->withJsonResponse($response, $payload);
  }

  public function manualVerifyUser (Request $request, ResponseInterface $response, array $args): ResponseInterface
  {
    //declare needed class objects
    $event_db = new EventModel();
    $user_db = new UserModel();
    $ticket_db = new EventTicketModel();
    $event_user_db = new EventTicketUserModel();

    $event_id = $args["event_id"];

    $data = $request->getParsedBody();

    $phone = $data["phone"];



    $country_code = substr($phone, 0, 4);

    $rest_of_phone_number = substr($phone, 4);

    if(strlen($rest_of_phone_number) == 11 && $rest_of_phone_number[0] === "0")
    {
      $rest_of_phone_number = substr($rest_of_phone_number, 1);
    }

    $country_code_clean = substr($country_code, 1);

    $phone_clean = $country_code_clean.$rest_of_phone_number;



    $user = $user_db->where("user_phone",$phone_clean);

    if($user->count() < 1)
    {
      $error = ["errorMessage" => "User Not Found", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $user_details = $user->first();
    $user_id = $user_details->user_id;
    $user_fullname = $user_details->user_fullname;
    $user_pics = $user_details->user_picture;

    $attendee_query = $ticket_db->join('event', 'event_ticket.event_id', '=', 'event.event_id')
    ->join('event_ticket_users', 'event_ticket.event_ticket_id', '=', 'event_ticket_users.event_ticket_id')
    ->select('event_ticket_users.event_ticket_user_id','event_ticket.ticket_name')
    ->where("event_ticket_users.user_id",$user_id)->where("event_ticket.event_id",$event_id)->where("event_ticket_users.ownership_status",Constants::EVENT_TICKET_ACTIVE);

    if($attendee_query->count() < 1)
    {
      $error = ["errorMessage" => "User Not registered for event", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $attendee_details = $attendee_query->first();
    $ticket_name = $attendee_details->ticket_name;
    $event_ticket_id = $attendee_details->event_ticket_user_id;

    $event_user_db->where("event_ticket_user_id",$event_ticket_id)->update(["status" => Constants::EVENT_TICKET_USED]);

    $response_data = [
      "ticket_name" => $ticket_name,
      "fullname" => $user_fullname,
      "user_pics" => $user_pics
    ];

    $payload = ["statusCode" => 200, "data" => $response_data];
    return $this->json->withJsonResponse($response, $payload);
  }

}
