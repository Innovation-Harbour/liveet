<?php

namespace Liveet\APIs;

use Liveet\Domain\Constants;
use stdClass;

/**
 * Undocumented class
 */
class TermiiAPI
{
  private $productionProtocol = "https://";
  private $productionBaseUrl = "termii.com/api";
  private $productionPort = 443;

  private $stagingProtocol = "https://";
  private $stagingBaseUrl = "termii.com/api";
  private $stagingPort = 443;

  private function setupRequest($path, $method = "GET", $body = null, $headers = [])
  {
    $url = "";
    $port = "";

    if ($_SERVER['HTTP_HOST'] == Constants::PRODUCTION_HOST) {
      $url =  $this->productionProtocol . $this->productionBaseUrl;
      $port = $this->productionPort;
    } else {
      $url = $this->stagingProtocol . $this->stagingBaseUrl;
      $port = $this->stagingPort;
    }

    $url = $url . $path;

    $headers[] = "Content-Type: application/json";

    $curl = curl_init();

    $options =  array(
      CURLOPT_URL => $url,
      CURLOPT_PORT => $port,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_CUSTOMREQUEST => $method,
    );

    if (isset($body)) {
      $options[CURLOPT_POSTFIELDS] = json_encode($body);
    }

    curl_setopt_array($curl, $options);

    $errorResponse = new stdClass();
    $errorResponse->type = "error";
    $errorResponse->message = "Termii Server: " . curl_error($curl);

    $response = curl_exec($curl);
    $response = $response ? $response : json_encode($errorResponse);

    curl_close($curl);

    return json_decode($response);
  }

  public function sendSMS($phone, $message)
  {
    return $this->setupRequest("/sms/send", "POST", [
        "api_key" => $_ENV["TERMII_KEY"],
        "to" => $phone,
        "from" => "N-Alert",
        "sms" => $message,
        "type" => "plain",
        "channel" => "dnd"
    ]);
  }
}
