<?php
namespace Liveet\Controllers\Mobile\Helper;

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
}
