<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Models\InvitationModel;
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
    $json = new JSON();
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

      $can_invite = ($result->event_can_invite === "CAN_INVITE") ? true : false;
      $isFavourite = ($result->event_favourite_id !== null) ? true : false;

      $tmp = [
        "event_id" => intval($result->event_id),
        "event_image" => $result->event_multimedia,
        "event_title" => $result->event_name,
        "event_date" => intval($date),
        "event_month" => $month,
        "can_invite" => $can_invite,
        "is_favourite" => $isFavourite,
      ];

      array_push($response_data,$tmp);
    }

    $payload = ["statusCode" => 200, "data" => $response_data];

    return $json->withJsonResponse($response, $payload);
  }

  public function DoEventFavourite (Request $request, ResponseInterface $response): ResponseInterface
  {
    $favourite_db = new FavouriteModel();

    $data = $request->getParsedBody();

    $user_id = $data["user_id"];
    $event_id = $data["event_id"];
    $favourite = $data["favourite"];

    var_dump($user_id,$event_id,$favourite);
    die;

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



}
