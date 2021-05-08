<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Models\InvitationModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\UserModel;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketUserModel;
use Illuminate\Support\Facades\DB;
use Liveet\Models\Mobile\FavouriteModel;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Rashtell\Domain\KeyManager;
use Aws\Rekognition\RekognitionClient;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventMobileController extends BaseController {
  use LiveetFunction;

  public function __construct (){
    $this->json = new JSON();
  }

  public function GetEvents (Request $request, ResponseInterface $response, array $args): ResponseInterface
  {
    //declare needed class objects
    $db = new InvitationModel();
    $ticket_db = new EventTicketModel();


    $response_data = [];

    $user_id = $args["user_id"];
    $offset = $args["offset"];
    $limit = $args["limit"];

    $results = $db->getMobileEvents($user_id, $offset, $limit);

    foreach($results as $result)
    {
      $datetime = $result->event_date_time;
      $date = date('d',$datetime);
      $month = date('M',$datetime);
      $year = date('Y',$datetime);

      $can_invite_count = intval($result->invitee_can_invite_count);

      $can_invite = ($result->event_can_invite === "CAN_INVITE" || ($result->event_can_invite === "CAN_INVITE_RESTRICTED" && $can_invite_count > 0)) ? true : false;
      $is_free = ($result->event_payment_type === "FREE") ? true : false;
      $isFavourite = ($result->event_favourite_id !== null) ? true : false;
      $useMap = ($result->location_lat !== null || $result->location_long !== null) ? true : false;

      $tmp = [
        "event_id" => intval($result->event_id),
        "event_image" => $result->event_multimedia,
        "event_title" => $result->event_name,
        "event_date" => intval($date),
        "event_month" => $month,
        "event_year" => $year,
        "event_venue" => $result->event_venue,
        "event_lat" => is_null($result->location_lat) ? 1.111111 : doubleval($result->location_lat),
        "event_long" => is_null($result->location_long) ? 1.11111 : doubleval($result->location_long),
        "can_invite" => $can_invite,
        "is_favourite" => $isFavourite,
        "is_free" => $is_free,
        "use_map" => $useMap,
      ];

      //check if the user already attending this event
      $eventQuery = $ticket_db->join('event', 'event_ticket.event_id', '=', 'event.event_id')
      ->join('event_ticket_users', 'event_ticket.event_ticket_id', '=', 'event_ticket_users.event_ticket_id')
      ->where("event_ticket.event_id",$result->event_id)->where("event_ticket_users.user_id",$user_id)->count();

      if($eventQuery < 1 && (intval($datetime) > time())){
        array_push($response_data,$tmp);
      }

    }

    $payload = ["statusCode" => 200, "data" => $response_data];

    return $this->json->withJsonResponse($response, $payload);
  }

  public function DoEventFavourite (Request $request, ResponseInterface $response): ResponseInterface
  {
    $favourite_db = new FavouriteModel();

    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $event_id = $data["event_id"];
    $favourite = $data["favourite"];

    $doFavourite = ($favourite === "true") ? true : false;

    $favourite_count = $favourite_db->where("event_id",$event_id)->where("user_id", $user_id)->count();

    if($doFavourite){
      if($favourite_count == 0)
      {
        $favourite_db->create([
            "event_id" => $event_id,
            "user_id" => $user_id
        ]);
      }
      $payload = ["statusCode" => 200, "successMessage" => "Event Favourite Added"];
    }
    else{
      //remove record from db
      if($favourite_count == 1)
      {
        $favourite_db->where("event_id",$event_id)->where("user_id", $user_id)->forceDelete();
      }
      $payload = ["statusCode" => 200, "successMessage" => "Event Favourite Deleted"];
    }

    return $this->json->withJsonResponse($response, $payload);
  }

  public function GetEventTickets (Request $request, ResponseInterface $response, array $args): ResponseInterface
  {
    //declare needed class objects
    $db = new EventTicketModel();

    $response_data = [];

    $event_id = $args["event_id"];

    $results = $db->where("event_id",$event_id)->get();

    foreach($results as $result)
    {
      $ticket_cost = intval($result->ticket_cost);
      $ticket_discount = intval($result->ticket_discount);
      $new_ticket_price = $ticket_cost - (($ticket_discount * $ticket_cost)/100);

      $readable_ticket_discount = $ticket_discount."%";

      $tmp = [
        "event_ticket_id" => intval($result->event_ticket_id),
        "ticket_name" => $result->ticket_name,
        "ticket_desc" => $result->ticket_desc,
        "ticket_cost" => $new_ticket_price,
        "ticket_discount" => $readable_ticket_discount,
        "is_selected" => false
      ];

      array_push($response_data,$tmp);
    }

    $payload = ["statusCode" => 200, "data" => $response_data];

    return $this->json->withJsonResponse($response, $payload);
  }

  public function doAttentEvent (Request $request, ResponseInterface $response): ResponseInterface
  {
    $user_db = new UserModel();
    $ticket_db = new EventTicketUserModel();
    $event_db = new EventModel();
    $invitation_db = new InvitationModel();

    $data = $request->getParsedBody();


    $event_id = $data["event_id"];
    $ticket_id = $data["ticket_id"];
    $user_id = $data["user_id"];
    $isFree = $data["is_free"] === "true" ? true : false;

    //get user details
    $query = $user_db->where("user_id",$user_id);

    if (!$query->exists()) {
      $error = ["errorMessage" => "User Not Found", "statusCode" => 400];

      return $this->json->withJsonResponse($response, $error);
    }

    $user_details = $user_db->where("user_id",$user_id)->first();

    $user_phone = $user_details->user_phone;
    $user_image_key = $user_details->image_key;

    //get event details
    $event_query = $event_db->where("event_id",$event_id);

    if (!$event_query->exists()) {
      $error = ["errorMessage" => "Event Not Found", "statusCode" => 400];

      return $this->json->withJsonResponse($response, $error);
    }

    $event_details = $event_db->where("event_id",$event_id)->first();
    $eventCode = $event_details->event_code;

    if ($ticket_db->where("event_ticket_id", $ticket_id)->where("user_id", $user_id)->exists()) {
        $error = ["errorMessage" => "User already registered for event", "statusCode" => 400];
        return $this->json->withJsonResponse($response, $error);
    }

    $aws_key = $_ENV["AWS_KEY"];
    $aws_secret = $_ENV["AWS_SECRET"];

    try{
      $recognition = new RekognitionClient([
  		    'region'  => 'us-west-2',
  		    'version' => 'latest',
  		    'credentials' => [
  		        'key'    => $aws_key,
  		        'secret' => $aws_secret,
  		    ]
  		]);
    }
    catch (\Exception $e){
      $error = ["errorMessage" => "Error connecting to image server. Please try again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    try{
      $result = $recognition->indexFaces([
				    'CollectionId' => $eventCode, // REQUIRED
				    'DetectionAttributes' => ['ALL'],
				    'Image' => [ // REQUIRED
              'S3Object' => [
                'Bucket' => 'liveet-users',
                'Name' => $user_image_key,
              ]
				    ]
				]);
    }
    catch (\Exception $e){
      $error = ["errorMessage" => "Error connecting to image server. Please try again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    if(!isset($result['FaceRecords'][0]['FaceDetail']['Gender']))
		{
      $error = ["errorMessage" => "Error connecting to image server. Please try again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
		}

    $face_id = $result['FaceRecords'][0]['Face']['FaceId'];

    $db_details = [
      "event_ticket_id" => $ticket_id,
      "user_id" => $user_id,
      "user_face_id" => $face_id,
      "status" => "TICKET_USED"
    ];

    $addTicketUser = $ticket_db->createSelf($db_details);

    if($invitation_db->where("event_id", $event_id)->where("event_invitee_user_phone", $user_phone)->exists())
    {
      $invitation_db->where("event_id", $event_id)->where("event_invitee_user_phone", $user_phone)->update(["event_invitation_status" => "ACCEPTED"]);
    }

    $payload = ["statusCode" => 200, "successMessage" => "Ticket Registered"];
    return $this->json->withJsonResponse($response, $payload);

  }

  public function getEventFavourites(Request $request, ResponseInterface $response, array $args): ResponseInterface
  {
    //declare needed class objects
    $db = new FavouriteModel();
    $ticket_db = new EventTicketModel();


    $response_data = [];

    $user_id = $args["user_id"];
    $offset = $args["offset"];
    $limit = $args["limit"];

    $results = $db->getUserFavourites($user_id, $offset, $limit);

    foreach($results as $result)
    {
      $datetime = $result->event_date_time;
      $date = date('d',$datetime);
      $month = date('M',$datetime);
      $year = date('Y',$datetime);

      $can_invite_count = intval($result->invitee_can_invite_count);

      $can_invite = ($result->event_can_invite === "CAN_INVITE" || ($result->event_can_invite === "CAN_INVITE_RESTRICTED" && $can_invite_count > 0)) ? true : false;
      $is_free = ($result->event_payment_type === "FREE") ? true : false;
      $isFavourite = ($result->event_favourite_id !== null) ? true : false;
      $useMap = ($result->location_lat !== null || $result->location_long !== null) ? true : false;

      $tmp = [
        "event_id" => intval($result->event_id),
        "event_image" => $result->event_multimedia,
        "event_title" => $result->event_name,
        "event_date" => intval($date),
        "event_month" => $month,
        "event_year" => $year,
        "event_venue" => $result->event_venue,
        "event_lat" => is_null($result->location_lat) ? 1.111111 : doubleval($result->location_lat),
        "event_long" => is_null($result->location_long) ? 1.11111 : doubleval($result->location_long),
        "can_invite" => $can_invite,
        "is_favourite" => $isFavourite,
        "is_free" => $is_free,
        "use_map" => $useMap,
      ];

      //check if the user already attending this event
      $eventQuery = $ticket_db->join('event', 'event_ticket.event_id', '=', 'event.event_id')
      ->join('event_ticket_users', 'event_ticket.event_ticket_id', '=', 'event_ticket_users.event_ticket_id')
      ->where("event_ticket.event_id",$result->event_id)->where("event_ticket_users.user_id",$user_id)->count();

      if($eventQuery < 1 && (intval($datetime) > time())){
        array_push($response_data,$tmp);
      }
    }

    $payload = ["statusCode" => 200, "data" => $response_data];

    return $this->json->withJsonResponse($response, $payload);
  }

}
