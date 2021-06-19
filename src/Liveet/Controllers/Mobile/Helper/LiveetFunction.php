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
}
