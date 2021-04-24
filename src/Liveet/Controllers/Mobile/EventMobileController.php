<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Models\InvitationModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\Mobile\FavouriteModel;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Rashtell\Domain\KeyManager;
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

      $can_invite = ($result->event_can_invite === "CAN_INVITE") ? true : false;
      $is_free = ($result->event_payment_type === "FREE") ? true : false;
      $isFavourite = ($result->event_favourite_id !== null) ? true : false;

      $tmp = [
        "event_id" => intval($result->event_id),
        "event_image" => $result->event_multimedia,
        "event_title" => $result->event_name,
        "event_date" => intval($date),
        "event_month" => $month,
        "event_year" => $year,
        "can_invite" => $can_invite,
        "is_favourite" => $isFavourite,
        "is_free" => $is_free,
      ];

      array_push($response_data,$tmp);
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

    $address = '1b Omorinre Johnson Close, Lekki, Lagos Nigeria'; // Address
    $prepAddr = str_replace(' ','+',$address);
    $apiKey = $_ENV["MAP_KEY"]; // Google maps now requires an API key.
    // Get JSON results from this request
    $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false&key='.$apiKey);
    $geo = json_decode($geo, true); // Convert the JSON to an array
    var_dump($geo);
    die;

    if (isset($geo['status']) && ($geo['status'] == 'OK')) {
      $latitude = $geo['results'][0]['geometry']['location']['lat']; // Latitude
      $longitude = $geo['results'][0]['geometry']['location']['lng']; // Longitude
    }

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



}
