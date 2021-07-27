<?php

namespace Liveet\Controllers\Mobile\Helper;

use Aws\Rekognition\RekognitionClient;
use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use GuzzleHttp\Client;
use Fcm\FcmClient;
use Fcm\Topic\Subscribe;
use Fcm\Topic\Unsubscribe;
use Fcm\Push\Notification;
use Liveet\Models\EventModel;
use Liveet\Models\EventTicketModel;
use Liveet\Models\EventTicketUserModel;
use Liveet\Models\TurnstileEventModel;
use Liveet\Models\Mobile\TempsModel;


/**
 * helper functions for Liveet Mobile
 */
trait LiveetFunction
{
  public function sendSMS($phone)
  {

    $curl = curl_init();
    $data = array(
      "api_key" => $_ENV["TERMII_KEY"],
      "message_type" => "NUMERIC",
      "to" => $phone,
      "from" => "N-Alert",
      "channel" => "dnd",
      "pin_attempts" => 10,
      "pin_time_to_live" =>  5,
      "pin_length" => 4,
      "pin_placeholder" => "< 1234 >",
      "message_text" => "Your Liveet pin is < 1234 >",
      "pin_type" => "NUMERIC"
    );

    $post_data = json_encode($data);

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://termii.com/api/sms/otp/send",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $post_data,
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  public function verifySMS($otp, $sms_pin)
  {
    $curl = curl_init();
    $data = array(
      "api_key" => $_ENV["TERMII_KEY"],
      "pin_id" => $sms_pin,
      "pin" => $otp,
    );

    $post_data = json_encode($data);

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://termii.com/api/sms/otp/verify",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $post_data,
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  public function createAwsEvent($event)
  {

    $json = new JSON();
    $aws_key = $_ENV["AWS_KEY"];
    $aws_secret = $_ENV["AWS_SECRET"];

    try {
      $recognition = new RekognitionClient([
        'region'  => 'us-west-2',
        'version' => 'latest',
        'credentials' => [
          'key'    => $aws_key,
          'secret' => $aws_secret,
        ]
      ]);

      $result = $recognition->createCollection([
        'CollectionId' => $event, // REQUIRED
      ]);

      $status = "done";
    } catch (\Exception $e) {
      $status = "error";
    }

    return $status;
  }

  public function getCoordinates($address)
  {
    $address_found = false;
    $apiKey = $_ENV["MAP_KEY"];
    try {
      $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $apiKey);
      $geo = json_decode($geo, true); // Convert the JSON to an array

      if (isset($geo['status']) && ($geo['status'] == 'OK')) {
        $latitude = $geo['results'][0]['geometry']['location']['lat']; // Latitude
        $longitude = $geo['results'][0]['geometry']['location']['lng']; // Longitude
        $address_found = true;
      }
    } catch (\Exception $e) {
      $address_found = false;
      $longitude = null;
      $latitude = null;
    }
    return [$address_found, $latitude, $longitude];
  }

  public function sendMobileNotification($notification_type, $title, $message, $user_tokens=false)
  {
    //initialize the necessary classes
    $server_key = $_ENV["FCM_SERVER_KEY"];
    $server_id = $_ENV["FCM_SENDER_ID"];

    try{
      $fcm_client = new FcmClient($server_key,$server_id);
      $notification = new Notification();
    } catch (\Exception $e) {
      var_dump($e->getMessage());
      return false;
    }

    if($notification_type === Constants::NOTIFICATION_ONE_USER)
    {
      $notification
        ->addRecipient($user_tokens)
        ->setTitle($title)
        ->setSound("default")
        ->setBody($message);

        try{
          $fcm_client->send($notification);
        } catch (\Exception $e) {
          return false;
        }
    }
    else if ($notification_type === Constants::NOTIFICATION_USER_GROUP)
    {
      $topic = "/topics/".$user_tokens;
      $notification
        ->addRecipient($topic)
        ->setTitle($title)
        ->setBody($message);

        try{
          $fcm_client->send($notification);
        } catch (\Exception $e) {
          return false;
        }
    }
    else if ($notification_type === Constants::NOTIFICATION_ALL_USER)
    {
      $topic = "/topics/all";
      $notification
        ->addRecipient($topic)
        ->setTitle($title)
        ->setBody($message);

        try{
          $fcm_client->send($notification);
        } catch (\Exception $e) {
          return false;
        }
    }
    else{
      return false;
    }

    return true;

  }

  public function subcribeUser($topic, $token)
  {
    $server_key = $_ENV["FCM_SERVER_KEY"];
    $server_id = $_ENV["FCM_SENDER_ID"];

    try{
      $fcm_client = new FcmClient($server_key,$server_id);
      $subscribe = new Subscribe($topic);

      $subscribe->addDevice($token);

      $fcm_client->send($subscribe);

    } catch (\Exception $e) {
      return false;
    }

    return true;
  }

  public function unSubcribeUser($topic, $token)
  {
    $server_key = $_ENV["FCM_SERVER_KEY"];
    $server_id = $_ENV["FCM_SENDER_ID"];

    try{
      $fcm_client = new FcmClient($server_key,$server_id);
      $unsubscribe = new Unsubscribe($topic);

      $unsubscribe->addDevice($token);

      $fcm_client->send($unsubscribe);

    } catch (\Exception $e) {
      return false;
    }

    return true;
  }

  public function checkFaceMatchForEvent($base64,$event_identifier,$from_mqtt = false)
  {
    $is_approved = false;
    $ticket_name  = false;
    $user_id = false;
    $event_db = new EventModel();
    $ticket_db = new EventTicketModel();
    $event_user_db = new EventTicketUserModel();
    $turnstile_db = new TurnstileEventModel();
    $ticket_id = false;


    if($from_mqtt)
    {
      $turnstile_id = $event_identifier;

      $turnstile_query = $turnstile_db->join('turnstile', 'turnstile_event.turnstile_id', '=', 'turnstile.turnstile_id')
      ->join('event_ticket', 'turnstile_event.event_ticket_id', '=', 'event_ticket.event_ticket_id')
      ->select('event_ticket.event_ticket_id','event_ticket.event_id')
      ->where("turnstile.turnstile_name",$turnstile_id);

      var_dump($turnstile_query->count());
      die;

      if($turnstile_query->count() < 1)
      {
        return [$is_approved,$ticket_name,$user_id];
      }

      $turnstile_details = $turnstile_query->first();
      $event_id = $turnstile_details->event_id;
      $ticket_id = $turnstile_details->event_ticket_id;
    }
    else{
      $event_id = $event_identifier;
    }

    $byte_image = base64_decode($base64);

    $event_details = $event_db->where("event_id", $event_id)->first();
    $event_code = $event_details->event_code;



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

      $img_result = $recognition->searchFacesByImage([ // REQUIRED
  		    'CollectionId' => $event_code,
          'FaceMatchThreshold' => 95.0,
  		    'Image' => [ // REQUIRED
            'Bytes' => $byte_image,
  		    ],
          'MaxFaces' => 1
  		]);

    }
    catch (\Exception $e){
      return [$is_approved,$ticket_name,$user_id];
    }

    if(isset($img_result["FaceMatches"][0]["Face"]["FaceId"]))
    {
      $similarity = round($img_result["FaceMatches"][0]["Similarity"]);

      if($similarity > 95)
      {
        $face_id = $img_result["FaceMatches"][0]["Face"]["FaceId"];

        if($from_mqtt)
        {
          $event_user = $event_user_db->where("user_face_id",$face_id)->where("event_ticket_id",$ticket_id)->where("ownership_status",Constants::EVENT_TICKET_ACTIVE);
        }
        else {
          $event_user = $event_user_db->where("user_face_id",$face_id)->where("ownership_status",Constants::EVENT_TICKET_ACTIVE);
        }

        if($event_user->count() == 1)
        {
          $user_details =  $event_user->first();
          $ticket_id = $user_details->event_ticket_id;
          $user_id = $user_details->user_id;

          $ticket_details = $ticket_db->where("event_ticket_id", $ticket_id)->first();
          $ticket_name = $ticket_details->ticket_name;

          //update the ticket as used
          $event_user->update(["status" => Constants::EVENT_TICKET_USED]);
          $is_approved = true;
        }
      }

    }
     return [$is_approved,$ticket_name,$user_id];
  }
}
