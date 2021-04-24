<?php
namespace Liveet\Controllers\Mobile\Helper;

use Aws\Rekognition\RekognitionClient;
use Rashtell\Domain\JSON;

/**
 * helper functions for Liveet Mobile
 */
trait LiveetFunction
{
  public function sendSMS($phone){

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
       "message_text" => "Your Liveet registration pin is < 1234 >",
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

  public function verifySMS($otp,$sms_pin){
    $curl = curl_init();
    $data = array (
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

  public function createAwsEvent($event){

    $json = new JSON();
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

      $result = $recognition->createCollection([
  		    'CollectionId' => $event, // REQUIRED
  		]);

      $status = "done";
    }
    catch (\Exception $e){
      $status = "error";
    }

    return $status;
  }

  public function getCoordinates($address){
    $address_found = false;
    $apiKey = $_ENV["MAP_KEY"];
    try{
      $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false&key='.$apiKey);
      $geo = json_decode($geo, true); // Convert the JSON to an array

      if (isset($geo['status']) && ($geo['status'] == 'OK')) {
        $latitude = $geo['results'][0]['geometry']['location']['lat']; // Latitude
        $longitude = $geo['results'][0]['geometry']['location']['lng']; // Longitude
        $address_found = true;
      }
    }
    catch(\Exception $e)
    {
      $address_found = false;
      $longitude = 0;
      $latitude = 0;
    }
    return[$address_found,$latitude,$longitude];
  }
}
