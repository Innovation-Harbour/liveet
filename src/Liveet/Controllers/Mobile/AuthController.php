<?php

namespace Liveet\Controllers\Mobile;

use Rashtell\Domain\JSON;
use Liveet\Domain\Constants;
use Liveet\Models\Mobile\TempModel;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;
use Liveet\Models\UserModel;
use Liveet\Domain\MailHandler;
use Liveet\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Aws\Rekognition\RekognitionClient;
use Aws\S3\S3Client;
use Rashtell\Domain\KeyManager;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends BaseController {
  use LiveetFunction;

  public function Register (Request $request, ResponseInterface $response): ResponseInterface
  {
    $eligible_phone_starting = array("6","7","8","9");

    //declare needed class objects
    $json = new JSON();
    $user_db = new UserModel();
    $temp_db = new TempModel();

    $data = $request->getParsedBody();

    $phone = $data["phone"];

    $country_code = substr($phone, 0, 4);

    $rest_of_phone_number = substr($phone, 4);

    if(strlen($rest_of_phone_number) == 11 && $rest_of_phone_number[0] === "0")
    {
      $rest_of_phone_number = substr($rest_of_phone_number, 1);
    }

    $phone_count = strlen($rest_of_phone_number);

    if ($country_code !=="+234")
    {
      $error = ["errorMessage" => "Selected Country not supported at the moment for now", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }

    if($phone_count == 10 && in_array($rest_of_phone_number[0], $eligible_phone_starting))
    {
      $country_code_clean = substr($country_code, 1);

      $phone_clean = $country_code_clean.$rest_of_phone_number;

      $phone_full = $country_code.$rest_of_phone_number;

      $user_count = $user_db->where('user_phone', $phone_clean)->count();
      $temp_count = $temp_db->where('temp_phone', $phone_clean)->count();

      if($user_count > 0)
      {
        $error = ["errorMessage" => "Phone Number Already Registered", "statusCode" => 400];

        return $json->withJsonResponse($response, $error);
      }

      // here we send sms
      $sms_response = json_decode($this->sendSMS($phone_clean),true);

      $sms_status = $sms_response['smsStatus'];

      if($sms_status !== "Message Sent")
      {
        $error = ["errorMessage" => "Error sending SMS. Please Register Again", "statusCode" => 400];
        return $json->withJsonResponse($response, $error);
      }

      $sms_pin = $sms_response['pinId'];

      if($temp_count < 1)
      {
        $temp_db->create(["temp_phone" => $phone_clean]);
      }

      $data_to_view = ["country_code" => $country_code, "Phone_Number" => $phone_full, "sms_pin" => $sms_pin];

      $payload = ["statusCode" => 200, "data" => $data_to_view];

      return $json->withJsonResponse($response, $payload);
    }
    else{
      $error = ["errorMessage" => "Phone Number Does Not Match The Number Format for Selected Country", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }
  }

  public function Login (Request $request, ResponseInterface $response): ResponseInterface
  {

    //declare needed class objects
    $json = new JSON();
    $user_db = new UserModel();
    $temp_db = new TempModel();
    $keymanager = new KeyManager();

    $data = $request->getParsedBody();

    $email = $data["email"];
    $password = $data["password"];

    $hashed_password = hash('sha256',$password);


    $user_count = $user_db->where('user_email', $email)->count();

    if($user_count < 1)
    {
      $error = ["errorMessage" => "Email Not Registered. Please Try Again", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }

    $user_data = $user_db->where('user_email', $email)->take(1)->get();
    $user_data_clean = $user_data[0];

    $db_password = $user_data_clean->user_password;

    if($hashed_password !== $db_password)
    {
      $error = ["errorMessage" => "Password Not Correct. Please Try Again", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }

    //create user auth token

    $user_data_token[] = [
      "email" => $email
    ];

    $token = $keymanager->createClaims($user_data_token);

    //get user data
    $fullname = $user_data_clean->user_fullname;
    $user_id = $user_data_clean->user_id;

    $data_to_view = ["email" => $email, "token" => $token, "name" => $fullname,"user_id" => $user_id];

    $payload = ["statusCode" => 200, "data" => $data_to_view];

    return $json->withJsonResponse($response, $payload);

  }

  public function VerifyOTP (Request $request, ResponseInterface $response): ResponseInterface
  {
    $event_code = "TestEventID1234";

    $result = $this->awsAddEvent($event_code);

    var_dump($result);
    die;



    /*
    //declare needed class objects
    $json = new JSON();

    $data = $request->getParsedBody();

    $phone = $data["phone"];
    $sms_pin = $data["sms_pin"];
    $otp = $data["otp"];

    //verify OTP with Termii
    $sms_response = json_decode($this->verifySMS($otp,$sms_pin),true);

    $otp_status = $sms_response['verified'];

    $is_accepted = ($otp_status) ? true : false;

    if($is_accepted)
    {
      $data_to_view = ["sent_otp" => $otp, "Phone Number" => $phone];

      $payload = ["statusCode" => 200, "data" => $data_to_view];

      return $json->withJsonResponse($response, $payload);
    }
    else{
      $error = ["errorMessage" => "Provided OTP Not Correct.", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }
    */
  }

  public function ResendOTP (Request $request, ResponseInterface $response): ResponseInterface
  {
    //declare needed class objects
    $json = new JSON();

    $data = $request->getParsedBody();

    $phone = $data["phone"];

    $phone_clean = substr($phone, 1);

    // here we send sms
    $sms_response = json_decode($this->sendSMS($phone_clean),true);

    $sms_status = $sms_response['smsStatus'];

    if($sms_status !== "Message Sent")
    {
      $error = ["errorMessage" => "Error sending SMS. Please Register Again", "statusCode" => 400];
      return $json->withJsonResponse($response, $error);
    }

    $sms_pin = $sms_response['pinId'];


    $data_to_view = ["country_code" => $country_code, "Phone_Number" => $phone, "sms_pin" => $sms_pin];

    $payload = ["statusCode" => 200, "data" => $data_to_view];

    return $json->withJsonResponse($response, $payload);

  }

  public function CompleteProfile (Request $request, ResponseInterface $response): ResponseInterface
  {
    //declare needed class objects
    $json = new JSON();
    $user_db = new UserModel();
    $temp_db = new TempModel();

    $data = $request->getParsedBody();

    $phone = $data["phone"];
    $name = $data["name"];
    $email = $data["email"];
    $password = $data["password"];
    $repeat_password = $data["repeat_password"];

    $phone_clean = substr($phone, 1);


    //checks

    $user_count = $user_db->where('user_email', $email)->count();
    $temp_count = $temp_db->where('temp_phone', $phone_clean)->count();

    if ($user_count > 0)
    {
      $error = ["errorMessage" => "Email already Registered. Please use another email address", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }

    if ($temp_count < 1)
    {
      $error = ["errorMessage" => "Error Occured While Registering. Please try Registering again", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }

    if ($password !== $repeat_password)
    {
      $error = ["errorMessage" => "Password & Repeat Password Do not match. Please Try Again", "statusCode" => 400];

      return $json->withJsonResponse($response, $error);
    }

    //after checks passed, update temp table

    $crypt_password = hash('sha256', $password);

    $temp_db->where('temp_phone', $phone_clean)->update(["temp_name" => $name, "temp_email" => $email, "temp_password" => $crypt_password]);

    $payload = ["statusCode" => 200, "successMessage" => "Temp Details Added"];

    return $json->withJsonResponse($response, $payload);
  }

  public function CompleteRegistration (Request $request, ResponseInterface $response): ResponseInterface
  {
    //declare needed class objects
    $json = new JSON();
    $keymanager = new KeyManager();
    $user_db = new UserModel();
    $temp_db = new TempModel();

    $data = $request->getParsedBody();

    $phone = $data["phone"];
    $image = $data["image"];

    $byte_image = base64_decode($image);
  	$code = rand(00000000, 99999999);

    $phone_clean = substr($phone, 1);

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
      $error = ["errorMessage" => "Error connecting to image server. Please try Registering again", "statusCode" => 400];
      return $json->withJsonResponse($response, $error);
    }

    try{
      $s3 = new S3Client([
  		    'region'  => 'us-west-2',
  		    'version' => 'latest',
  		    'credentials' => [
  		        'key'    => $aws_key,
  		        'secret' => $aws_secret,
  		    ]
  		]);
    }
    catch (\Exception $e){
      $error = ["errorMessage" => "Error connecting to AWS s3. Please try Registering again", "statusCode" => 400];
      return $json->withJsonResponse($response, $error);
    }

    //push image to s3
    $key = 'user-'.$code.'-image.png';

    try{
      $s3_result = $s3->putObject([
          'Bucket' => 'liveet-users',
          'Key'    => $key,
          'Body'   => $byte_image,
          'ACL'    => 'public-read',
          'ContentType'    => 'image/png'
      ]);
    }
    catch (\Exception $e){
      $error = ["errorMessage" => "Error posting image to S3. Please try Registering again", "statusCode" => 400];
      return $json->withJsonResponse($response, $error);
    }

    $picture_url = "https://liveet-users.s3-us-west-2.amazonaws.com/".$key;

    //check if image is good and usable
    try{
      $result = $recognition->detectFaces([ // REQUIRED
  		    'Attributes' => ['ALL'],
  		    'Image' => [ // REQUIRED
            'S3Object' => [
            'Bucket' => 'liveet-users',
            'Name' => $key,
            ],
  		    ]
  		]);
    }
    catch(\Exception $e){
      $error = ["errorMessage" => "Error getting face recognition. Please try Registering again", "statusCode" => 400];
      return $json->withJsonResponse($response, $error);
    }

    $confidence = 0;
    $confidence = $result["FaceDetails"][0]["Gender"]["Confidence"];


    if($confidence > 50)
    {
      //get temp data and delete temp data from db
      $temp_data = $temp_db->where('temp_phone', $phone_clean)->take(1)->get();
      $temp_data_clean = $temp_data[0];
      //var_dump($temp_data[0]->temp_phone);
      //die();

      $fullname = $temp_data_clean->temp_name;
      $email = $temp_data_clean->temp_email;
      $password = $temp_data_clean->temp_password;



      //create user auth token

      $user_data_token[] = [
        "email" => $email
      ];

      $token = $keymanager->createClaims($user_data_token);

      //add data to user table
      try{
        $user_db->create([
            "user_fullname" => $fullname,
            "user_phone" => $phone_clean,
            "user_email" => $email,
            "user_password" => $password,
            "user_picture" => $picture_url,
            "image_key" => $key,
        ]);
      }
      catch (\Exception $e){
        var_dump($e->getMessage());
        die();
        $error = ["errorMessage" => $e->message(), "statusCode" => 400];
        return $json->withJsonResponse($response, $error);
      }

      $user_data = $user_db->where('user_phone', $phone_clean)->take(1)->get();
      $user_data_clean = $user_data[0];

      $user_id = $user_data_clean->user_id;


      //remove record from temp db
      $temp_db->where('temp_phone', $phone_clean)->delete();

      $data_to_view = ["email" => $email, "token" => $token, "name" => $fullname,"user_id" => $user_id];

      $payload = ["statusCode" => 200, "data" => $data_to_view];

      return $json->withJsonResponse($response, $payload);


    }
    else{
      $error = ["errorMessage" => "Image Not Accepted. Please take a selfie of your face alone", "statusCode" => 400];
      return $json->withJsonResponse($response, $error);
    }
  }

  public function AWSAddEvent(Request $request, ResponseInterface $response): ResponseInterface
  {

    $event_code = "TestEventID1234";

    $result = $this->awsAddEvent($event_code);

    var_dump($result);
    die;
  }

}
