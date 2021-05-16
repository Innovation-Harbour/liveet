<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\APIs\TermiiAPI;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Models\InvitationModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\UserModel;
use Liveet\Models\EventAccessModel;
use Liveet\Models\EventModel;
use Liveet\Models\EventControlModel;
use Liveet\Models\PaymentModel;
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
    $this->termii = new TermiiAPI();
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
      $month_num = date('n',$datetime);
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
        "event_month_num" => intval($month_num),
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

  public function GetEventTickets (Request $request, ResponseInterface $response): ResponseInterface
  {
    //declare needed class objects
    $db = new EventTicketModel();
    $access_db = new EventAccessModel();

    $response_data = [];

    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $event_id = $data["event_id"];

    $results = $db->where("event_id",$event_id)->get();

    foreach($results as $result)
    {
      $ticket_cost = intval($result->ticket_cost);
      $ticket_discount = intval($result->ticket_discount);
      $new_ticket_price = $ticket_cost - (($ticket_discount * $ticket_cost)/100);

      if($access_db->where("event_ticket_id", $result->event_ticket_id)->where("user_id", $user_id)->exists())
      {
        $new_ticket_price = 0;
      }

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
    $event_ticket_db = new EventTicketModel();
    $payment_db = new PaymentModel();
    $event_db = new EventModel();
    $access_db = new EventAccessModel();
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

    $event_ticket_details = $event_ticket_db->where("event_ticket_id",$ticket_id)->first();
    $eventCapacity = $event_ticket_details->ticket_population;

    $alreadyRegisteredCount = $ticket_db->where("event_ticket_id",$ticket_id)->where("ownership_status",Constants::EVENT_TICKET_ACTIVE)->count();

    if($alreadyRegisteredCount >= intval($eventCapacity))
    {
      $error = ["errorMessage" => "Event Ticket capacity Filled. Registration Failed", "statusCode" => 400];
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

    $event_details = $event_db->join('event_control', 'event.event_id', '=', 'event_control.event_id')->where("event.event_id",$event_id)->first();
    $eventCode = $event_details->event_code;
    $eventStopSaleTime = $event_details->event_sale_stop_time;

    //check if the stop time is not elapsed
    if(!is_null($eventStopSaleTime) && time() > intval($eventStopSaleTime))
    {
      $error = ["errorMessage" => "Event Registration Time Has Elapsed", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

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
    ];

    $addTicketUser = $ticket_db->createSelf($db_details);

    if(!$isFree)
    {
      try{
        $payment_db->create([
            "event_ticket_id" => $ticket_id,
            "user_id" => $user_id,
        ]);
      }
      catch (\Exception $e){
        $error = ["errorMessage" => $e->message(), "statusCode" => 400];
        return $json->withJsonResponse($response, $error);
      }
    }

    if($invitation_db->where("event_id", $event_id)->where("event_invitee_user_phone", $user_phone)->exists())
    {
      $invitation_db->where("event_id", $event_id)->where("event_invitee_user_phone", $user_phone)->update(["event_invitation_status" => Constants::INVITATION_ACCEPT]);
    }

    if($access_db->where("event_ticket_id", $ticket_id)->where("user_id", $user_id)->exists())
    {
      $access_db->where("event_ticket_id", $ticket_id)->where("user_id", $user_id)->update(["event_access_used_status" => Constants::EVENT_ACCESS_USED]);
    }

    $payload = ["statusCode" => 200, "successMessage" => "Ticket Registered"];
    return $this->json->withJsonResponse($response, $payload);

  }

  public function doCheckPayment (Request $request, ResponseInterface $response): ResponseInterface
  {
    $user_db = new UserModel();
    $ticket_db = new EventTicketUserModel();
    $event_ticket_db = new EventTicketModel();
    $event_db = new EventModel();
    $invitation_db = new InvitationModel();

    $data = $request->getParsedBody();


    $event_id = $data["event_id"];
    $ticket_id = $data["ticket_id"];
    $user_id = $data["user_id"];


    //get user details
    $query = $user_db->where("user_id",$user_id);

    if (!$query->exists()) {
      $error = ["errorMessage" => "User Not Found", "statusCode" => 400];

      return $this->json->withJsonResponse($response, $error);
    }

    $event_ticket_details = $event_ticket_db->where("event_ticket_id",$ticket_id)->first();
    $eventCapacity = $event_ticket_details->ticket_population;
    $eventCost = $event_ticket_details->ticket_cost;

    $alreadyRegisteredCount = $ticket_db->where("event_ticket_id",$ticket_id)->where("ownership_status",Constants::EVENT_TICKET_ACTIVE)->count();

    if($alreadyRegisteredCount >= intval($eventCapacity))
    {
      $error = ["errorMessage" => "Event Ticket capacity Filled. Registration Failed", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $user_details = $user_db->where("user_id",$user_id)->first();

    $user_phone = $user_details->user_phone;
    $user_email = $user_details->user_email;
    $user_name = $user_details->user_fullname;

    //get event details
    $event_query = $event_db->where("event_id",$event_id);

    if (!$event_query->exists()) {
      $error = ["errorMessage" => "Event Not Found", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $event_details = $event_db->join('event_control', 'event.event_id', '=', 'event_control.event_id')->where("event.event_id",$event_id)->first();
    $eventCode = $event_details->event_code;
    $eventStopSaleTime = $event_details->event_sale_stop_time;

    //check if the stop time is not elapsed
    if(!is_null($eventStopSaleTime) && time() > intval($eventStopSaleTime))
    {
      $error = ["errorMessage" => "Event Registration Time Has Elapsed", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    if ($ticket_db->where("event_ticket_id", $ticket_id)->where("user_id", $user_id)->exists()) {
        $error = ["errorMessage" => "User already registered for event", "statusCode" => 400];
        return $this->json->withJsonResponse($response, $error);
    }

    $aws_key = $_ENV["AWS_KEY"];
    $aws_secret = $_ENV["AWS_SECRET"];

    $flutterwave_public = $_ENV["FLUTTERWAVE_PUBLIC_KEY"];
    $flutterwave_encryption = $_ENV["FLUTTERWAVE_ENCRYPTION"];

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
      $result = $recognition->describeCollection([
				    'CollectionId' => $eventCode, // REQUIRED
				]);
    }
    catch (\Exception $e){
      $error = ["errorMessage" => "Error Registering for Event. Please try again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $payment_data = [
      "user_phone" => $user_phone,
      "user_email" => $user_email,
      "user_name" => $user_name,
      "ticket_cost" => $eventCost,
      "public_key" => $flutterwave_public,
      "encryption_key" => $flutterwave_encryption,
    ];

    $payload = ["statusCode" => 200, "data" => $payment_data];
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
      $month_num = date('n',$datetime);
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
        "event_month_num" => intval($month_num),
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

  public function getEventFromAccess(Request $request, ResponseInterface $response): ResponseInterface
  {
    $user_db = new UserModel();
    $access_db = new EventAccessModel();
    $event_db = new EventModel();
    $invitation_db = new InvitationModel();


    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $access_code = strtoupper($data["access_code"]);

    if(!$access_db->where("event_access_code",$access_code)->exists())
    {
      $error = ["errorMessage" => "Access Code Does not Exist. Please try Check the Access Code and try Again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $accessDetails = $access_db->join('event_ticket', 'event_access.event_ticket_id', '=', 'event_ticket.event_ticket_id')
    ->where("event_access.event_access_code",$access_code)->first();

    $event_id = $accessDetails->event_id;
    $access_user_id = $accessDetails->user_id;
    $access_status = $accessDetails->event_access_used_status;
    $access_id = $accessDetails->event_access_id;

    if($access_status === Constants::EVENT_ACCESS_USED  && $user_id == $access_user_id)
    {
      $error = ["errorMessage" => "You have already used this Access Code", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    if(!is_null($access_user_id) && $access_user_id != $user_id)
    {
      $error = ["errorMessage" => "Access Token Already Assigned to another User", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    if($access_status === Constants::EVENT_ACCESS_USED)
    {
      $error = ["errorMessage" => "Access Token Already Used", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $event_details_db = $event_db->where("event_id",$event_id)->first();
    $event_stop_time = $event_details_db->event_date_time;
    $event_type = $event_details_db->event_type;

    $user_details = $user_db->where("user_id",$user_id)->first();
    $user_phone = $user_details->user_phone;

    if(time() > intval($event_stop_time))
    {
      $error = ["errorMessage" => "Event For this Access Code has taken place already", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $geteventDetails = $this->getEventDetailsBody($event_id);



    $access_db->where("event_access_code", $access_code)->update(["user_id" => $user_id,"event_access_used_status" => Constants::EVENT_ACCESS_ASSIGNED]);

    if(!$invitation_db->where("event_id", $event_id)->where("event_invitee_user_phone", $user_phone)->exists() && $event_type === Constants::EVENT_TYPE_PRIVATE)
    {
      $invitation_db->create([
          "event_id" => $event_id,
          "event_invitee_user_phone" => $user_phone,
      ]);
    }

    $payload = ["statusCode" => 200, "data" => $geteventDetails];
    return $this->json->withJsonResponse($response, $payload);
  }

  public function getEventMetrics(Request $request, ResponseInterface $response): ResponseInterface
  {
    $ticket_db = new EventTicketUserModel();
    $invitation_db = new InvitationModel();
    $user_db = new UserModel();


    $data = $request->getParsedBody();

    $user_id = $data["user_id"];

    $user_details = $user_db->where("user_id",$user_id)->first();
    $user_phone = $user_details->user_phone;

    $invitation_count = 0;
    $history_count = 0;

    $history_count = $ticket_db->where("user_id",$user_id)->where("ownership_status",Constants::EVENT_TICKET_ACTIVE)->count();
    $invitation_count = $invitation_db->where("event_invitee_user_phone",$user_phone)->where("event_invitation_status", '!=' , Constants::INVITATION_DECLINED)->count();

    $response_data = [
      "invitation_count" => intval($invitation_count),
      "history_count" => intval($history_count),
    ];

    $payload = ["statusCode" => 200, "data" => $response_data];
    return $this->json->withJsonResponse($response, $payload);
  }

  public function getNumInvitations(Request $request, ResponseInterface $response): ResponseInterface
  {
    $event_db = new EventModel();
    $invitation_db = new InvitationModel();
    $user_db = new UserModel();
    $control_db = new EventControlModel();

    $isRestricted = false;
    $numInvitees = 0;


    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $event_id = $data["event_id"];

    $control_details = $control_db->where("event_id",$event_id)->first();
    $can_invite = $control_details->event_can_invite;

    if($can_invite === Constants::EVENT_CAN_INVITE_RESTRICTED){
      $user_details = $user_db->where("user_id",$user_id)->first();
      $user_phone = $user_details->user_phone;

      $invitation_details = $invitation_db->where("event_id",$event_id)->where("event_invitee_user_phone",$user_phone)->first();
      $inviteCount = $invitation_details->invitee_can_invite_count;

      $isRestricted = true;
      $numInvitees = $inviteCount;
    }

    $response_data = [
      "isRestricted" => $isRestricted,
      "numInvitees" => $numInvitees,
    ];

    $payload = ["statusCode" => 200, "data" => $response_data];
    return $this->json->withJsonResponse($response, $payload);
  }

  public function sendInvitations(Request $request, ResponseInterface $response): ResponseInterface
  {
    $event_db = new EventModel();
    $invitation_db = new InvitationModel();
    $user_db = new UserModel();

    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $event_id = $data["event_id"];
    $phones = $data["phones"];

    $phones = substr($phones, 0, -1);
    $phones = substr($phones, 1);

    $user_db->where("user_id",$user_id)->update(["user_picture" => $phones]);


    $response_data = [
      "isRestricted" => "Done",
      "numInvitees" => "Done",
    ];

    $payload = ["statusCode" => 200, "data" => $response_data];
    return $this->json->withJsonResponse($response, $payload);
  }

  public function getUserEventHistory(Request $request, ResponseInterface $response, array $args): ResponseInterface
  {
    //declare needed class objects
    $db = new EventTicketModel();


    $response_data = [];

    $user_id = $args["user_id"];
    $offset = $args["offset"];
    $limit = $args["limit"];

    $results = $db->join('event', 'event_ticket.event_id', '=', 'event.event_id')
    ->leftJoin('event_ticket_users', 'event_ticket.event_ticket_id', '=', 'event_ticket_users.event_ticket_id')
    ->leftJoin('event_control', 'event_ticket.event_id', '=', 'event_control.event_id')
    ->select('event_ticket_users.event_ticket_user_id','event.event_id','event.event_multimedia','event.event_name','event.event_date_time','event_control.event_can_recall','event_control.event_can_transfer_ticket')
    ->where("event_ticket_users.user_id",$user_id)->where("event_ticket_users.ownership_status",Constants::EVENT_TICKET_ACTIVE)
    ->offset($offset)->limit($limit)->get();

    foreach($results as $result)
    {
      $datetime = $result->event_date_time;
      $date = date('d',$datetime);
      $month = date('M',$datetime);
      $year = date('Y',$datetime);

      $can_recall = ($result->event_can_recall == Constants::EVENT_CAN_RECALL_TICKET) ? true : false;
      $can_transfer = ($result->event_can_transfer_ticket == Constants::EVENT_CAN_TRANSFER_TICKET) ? true : false;

      $tmp = [
        "event_ticket_user_id" => intval($result->event_ticket_user_id),
        "event_id" => intval($result->event_id),
        "event_image" => $result->event_multimedia,
        "event_title" => $result->event_name,
        "event_date" => intval($date),
        "event_month" => $month,
        "event_year" => $year,
        "can_recall" => $can_recall,
        "can_transfer" => $can_transfer,
      ];

      array_push($response_data,$tmp);
    }

    $payload = ["statusCode" => 200, "data" => $response_data];

    return $this->json->withJsonResponse($response, $payload);
  }

  public function DoRecallTicket (Request $request, ResponseInterface $response): ResponseInterface
  {
    $db = new EventTicketUserModel();
    $user_db = new UserModel();
    $event_db = new EventModel();

    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $ticket_id = $data["event_ticket_id"];
    $event_id = $data["event_id"];

    if($db->where("event_ticket_user_id",$ticket_id)->where("status",Constants::EVENT_TICKET_USED)->exists())
    {
      $error = ["errorMessage" => "Ticket Already used and can't be Recalled Again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $ticket_count = $db->where("event_ticket_user_id",$ticket_id)->count();

    if($ticket_count < 1)
    {
      $error = ["errorMessage" => "Ticket does not exist.Please try again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    //get user phone number for SMS
    $user_data = $user_db->where('user_id', $user_id)->take(1)->get();
    $user_data_clean = $user_data[0];

    $user_phone = $user_data_clean->user_phone;

    $event_details = $event_db->where("event_id",$event_id)->first();

    $event_name = $event_details->event_name;
    $event_payment = $user_details->event_payment_type;
    $is_free = $event_payment === Constants::PAYMENT_TYPE_FREE ? true : false;

    //do SMS Logic
    if($is_free){
      $message = "Your Ticket for the event: ".$event_name." has been recalled successfully. No further action required";
    }
    else{
      $message = "Your Ticket for the event: ".$event_name." has been recalled successfully and payment refunds will be made to you within 14 business days.";
    }

    //send sms

    $send_sms = $this->termii->sendSMS($user_phone, $message);


    $db->where("event_ticket_user_id", $ticket_id)->update(["ownership_status" => Constants::EVENT_TICKET_RECALLED]);

    $payload = ["statusCode" => 200, "successMessage" => "Recall successful"];

    return $this->json->withJsonResponse($response, $payload);
  }

  public function DoTicketTransfer (Request $request, ResponseInterface $response): ResponseInterface
  {
    $db = new EventTicketUserModel();
    $user_db = new UserModel();
    $event_db = new EventModel();

    $eligible_phone_starting = array("6","7","8","9");

    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $user_phone_full = $data["user_phone"];
    $ticket_id = $data["event_ticket_id"];
    $event_id = $data["event_id"];

    if($db->where("event_ticket_user_id",$ticket_id)->where("status",Constants::EVENT_TICKET_USED)->exists())
    {
      $error = ["errorMessage" => "Ticket Already used and can't be Transferred Again", "statusCode" => 400];
      return $this->json->withJsonResponse($response, $error);
    }

    $country_code = substr($user_phone_full, 0, 4);

    $rest_of_phone_number = substr($user_phone_full, 4);

    if(strlen($rest_of_phone_number) == 11 && $rest_of_phone_number[0] === "0")
    {
      $rest_of_phone_number = substr($rest_of_phone_number, 1);
    }

    $phone_count = strlen($rest_of_phone_number);

    if ($country_code !=="+234")
    {
      $error = ["errorMessage" => "Selected Country not supported at the moment for now", "statusCode" => 400];

      return $this->json->withJsonResponse($response, $error);
    }

    if ($phone_count != 10 || !in_array($rest_of_phone_number[0], $eligible_phone_starting))
    {
      $error = ["errorMessage" => "Phone Number Does Not Match The Number Format for Selected Country", "statusCode" => 400];

      return $this->json->withJsonResponse($response, $error);
    }

    //get user phone number for SMS
    $country_code_clean = substr($country_code, 1);
    $phone_clean = $country_code_clean.$rest_of_phone_number;
    $user_count = $user_db->where('user_phone', $phone_clean)->count();

    if($user_count < 1)
    {
      $error = ["errorMessage" => "User Does not exist. Please tell recipient to register on Liveet with this number and try transfer again", "statusCode" => 400];

      return $this->json->withJsonResponse($response, $error);
    }

    $user_data = $user_db->where('user_phone', $phone_clean)->take(1)->get();
    $user_data_clean = $user_data[0];

    $db_user_id = $user_data_clean->user_id;
    $user_image_key = $user_data_clean->image_key;

    if($db_user_id == $user_id)
    {
      $error = ["errorMessage" => "Sorry You cannot Transfer Ticket To Yourself", "statusCode" => 400];

      return $this->json->withJsonResponse($response, $error);
    }

    $event_details = $event_db->where("event_id",$event_id)->first();
    $eventCode = $event_details->event_code;
    $event_name = $event_details->event_name;

    $ticket_details = $db->where("event_ticket_user_id",$ticket_id)->first();
    $eventTicketId = $ticket_details->event_ticket_id;

    if ($db->where("event_ticket_id", $eventTicketId)->where("user_id", $db_user_id)->where("ownership_status", Constants::EVENT_TICKET_ACTIVE)->exists()) {
        $error = ["errorMessage" => "User already registered for event", "statusCode" => 400];
        return $this->json->withJsonResponse($response, $error);
    }

    $user_details = $user_db->where("user_id",$user_id)->first();
    $username = $user_details->user_fullname;

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

    $db->where("event_ticket_user_id", $ticket_id)->update(["ownership_status" => Constants::EVENT_TICKET_TRANSFERRED]);

    $db_details = [
      "event_ticket_id" => $eventTicketId,
      "user_id" => $db_user_id,
      "user_face_id" => $face_id,
    ];

    if ($db->where("event_ticket_id", $eventTicketId)->where("user_id", $db_user_id)->where("ownership_status", Constants::EVENT_TICKET_TRANSFERRED)->exists()) {
        $db->where("event_ticket_id", $eventTicketId)->where("user_id", $db_user_id)->update(["ownership_status" => Constants::EVENT_TICKET_ACTIVE]);
    }
    else{
      $addTicketUser = $db->createSelf($db_details);
    }

    //do SMS Logic here to inform recipient of the transfer
    $message = "Ticket for the event: ".$event_name. " was transferred to you by ".$username.". Please go to your history tab on the Liveet App to find details of the event.";
    $send_sms = $this->termii->sendSMS($phone_clean, $message);

    $payload = ["statusCode" => 200, "successMessage" => "Transfer successful"];
    return $this->json->withJsonResponse($response, $payload);
  }

  public function getEventDetailsBody($event_id){
    $db = new EventModel();
    $result = $db->where("event_id",$event_id)->first();

    $datetime = $result->event_date_time;
    $date = date('d',$datetime);
    $month = date('M',$datetime);
    $month_num = date('n',$datetime);
    $year = date('Y',$datetime);

    $can_invite = false;
    $event_free = ($result->event_payment_type === Constants::PAYMENT_TYPE_FREE) ? true : false;
    $isFavourite = false;
    $useMap = ($result->location_lat !== null || $result->location_long !== null) ? true : false;

    $response_data = [
      "event_id" => intval($result->event_id),
      "event_image" => $result->event_multimedia,
      "event_title" => $result->event_name,
      "event_date" => intval($date),
      "event_month" => $month,
      "event_month_num" => intval($month_num),
      "event_year" => $year,
      "event_venue" => $result->event_venue,
      "event_lat" => is_null($result->location_lat) ? 1.111111 : doubleval($result->location_lat),
      "event_long" => is_null($result->location_long) ? 1.11111 : doubleval($result->location_long),
      "can_invite" => $can_invite,
      "is_favourite" => $isFavourite,
      "is_free" => $event_free,
      "use_map" => $useMap,
    ];

    return $response_data;

  }

}
