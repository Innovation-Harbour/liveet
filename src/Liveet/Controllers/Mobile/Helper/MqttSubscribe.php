<?php

namespace Liveet\Controllers\Mobile\Helper;

use Bluerhinos\phpMQTT;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;

Class MqttSubscribe {
  use LiveetFunction;

  public function runSubscription() {

    return "world";
    /*
    $server = $_ENV["MQTT_SERVER"];
    $port = $_ENV["MQTT_PORT"];
    $username = $_ENV["MQTT_USER"];
    $password = $_ENV["MQTT_PASSWORD"];
    $client_id = $_ENV["MQTT_CLIENT"];

    $mqtt = new phpMQTT($server, $port, $client_id);

    if(!$mqtt->connect(true, NULL, $username, $password)) {
    	exit(1);
    }

    $mqtt->debug = true;

    $topics['liveet/mqtt/housekeeping'] = array('qos' => 0, 'function' => 'procMsg');
    $mqtt->subscribe($topics, 0);

    while($mqtt->proc()) {

    }

    $mqtt->close();

    function procMsg($topic, $msg){
      if ($topic === "liveet/mqtt/housekeeping")
      {
        $newtopic[$msg] = array('qos' => 0, 'function' => 'procMsg');
        $mqtt->subscribe($newtopic, 0);
        echo "liveet housekeeping started";
      }
      else {
        echo "MQTT face process started";
        [$is_approved,$from_turnstile,$turnstile_id] = $this->verifyuserFromTurnStile($topic,$msg);

        if($from_turnstile && $is_approved)
        {
          $publishtopic = 'mqtt/face/'.$turnstile_id;

          $payload = [
            "operator" => "Unlock",
            "messageId" => "liveetPublish",
            "info" => [
              "facesluiceId" => $turnstile_id,
              "openDoor" => 1,
              "showInfo" => "please Open the door"
            ]
          ];

          $payload = json_encode($payload);
          $mqtt->publish($publishtopic,$payload, 0, false);
        }
        echo "MQTT face process finished";
      }
    }
    */
  }
}

$obj = new MqttSubscribe();
echo $obj->runSubscription();
?>
