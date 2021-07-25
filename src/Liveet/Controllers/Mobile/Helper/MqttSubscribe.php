<?php

namespace Liveet\Controllers\Mobile\Helper;

use Bluerhinos\phpMQTT;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;

Class MqttSubscribe {
  use LiveetFunction;


  public function __construct (){
    $this->server = $_ENV["MQTT_SERVER"];
    $this->port = $_ENV["MQTT_PORT"];
    $this->username = $_ENV["MQTT_USER"];
    $this->password = $_ENV["MQTT_PASSWORD"];
    $this->client_id = $_ENV["MQTT_CLIENT"];

    $this->mqtt = new phpMQTT($this->server, $this->port, $this->client_id);

    if(!$this->mqtt->connect(true, NULL, $this->username, $this->password)) {
    	exit(1);
    }
  }

  public function runSubscription() {

    $this->mqtt->debug = true;

    $topics['liveet/mqtt/housekeeping'] = array('qos' => 0, 'function' => 'procMsg');
    $this->mqtt->subscribe($topics, 0);

    while($this->mqtt->proc()) {

    }

    $this->mqtt->close();

    function procMsg($topic, $msg){
      if ($topic === "liveet/mqtt/housekeeping")
      {
        $newtopic[$msg] = array('qos' => 0, 'function' => 'procMsg');
        $this->mqtt->subscribe($newtopic, 0);
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
          $this->mqtt->publish($publishtopic,$payload, 0, false);
        }
        echo "MQTT face process finished";
      }
    }
  }
}

$obj = new MqttSubscribe();
$obj->runSubscription();
?>
