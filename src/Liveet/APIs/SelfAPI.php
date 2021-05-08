<?php

namespace Liveet\APIs;

use Liveet\Domain\Constants;
use stdClass;

class SelfAPI
{
  private $productionProtocol = "https://";
  private $productionBaseUrl = "liveet.rollcallservice.com";
  private $productionIndexPath = "";
  private $productionPort = 443;

  private $stagingProtocol = "http://";
  private $stagingBaseUrl = "localhost";
  private $stagignIndexPath = "/liveet/liveet-apis";
  private $stagingPort = 80;

  private $productionAuthKey = "bearer ";

  private $stagingAuthKey = "bearer ";

  private $authKey = null;

  public function __construct($authKey = null)
  {
    if ($authKey) {
      $this->authKey = $authKey;
    }
  }

  private function setupRequest($path, $method = "GET", $body = null, $headers = [])
  {
    $url = "";
    $port = "";

    if ($_SERVER['HTTP_HOST'] == Constants::PRODUCTION_HOST) {
      $url =  $this->productionProtocol . $this->productionBaseUrl . $this->productionIndexPath;
      $port = $this->productionPort;
      $this->authKey = $this->productionAuthKey;
    } else {
      $url = $this->stagingProtocol . $this->stagingBaseUrl . $this->stagignIndexPath;
      $port = $this->stagingPort;
      $this->authKey = $this->stagingAuthKey;
    }

    $url = $url . $path;

    if ($this->authKey) {
      $headers[] = "Token: " . $this->authKey;
    }

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
    $errorResponse->error = new stdClass();
    $errorResponse->error->message = "Self Server: " . curl_error($curl);
    $errorResponse->error->code = 1;

    $response = curl_exec($curl);
    $response = $response ? $response : json_encode($errorResponse);

    curl_close($curl);

    return json_decode($response);
  }
}
