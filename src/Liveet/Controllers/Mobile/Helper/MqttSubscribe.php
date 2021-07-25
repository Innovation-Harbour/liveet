<?php
namespace Liveet\Controllers\Mobile\Helper;

require('LiveetFunction.php');
require('phpMQTT.php');

use Bluerhinos\phpMQTT;
use Liveet\Controllers\Mobile\Helper\LiveetFunction;

Class MqttSubscribe {
  use LiveetFunction;
}

$server = "hairdresser.cloudmqtt.com";
$port = "15472";
$username = "glddlzok";
$password = "sdaH4U6uEqGl";
$client_id = "liveet_mqtt_subscriber";

$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

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
  $server = "hairdresser.cloudmqtt.com";
  $port = "15472";
  $username = "glddlzok";
  $password = "sdaH4U6uEqGl";
  $client_id = "liveet_mqtt_subscriber_3";

  $obj = new MqttSubscribe();

  $newmqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

  if(!$newmqtt->connect(true, NULL, $username, $password)) {
    exit(1);
  }

  if ($topic === "liveet/mqtt/housekeeping")
  {
    $newtopic[$msg] = array('qos' => 0, 'function' => 'procMsg');
    $newmqtt->subscribe($newtopic, 0);
    $newmqtt->close();
    echo "\t$msg\n\n";
    echo "liveet housekeeping started \n";
    $hello = $obj->testfunctioncall();
    echo "\t$hello\n\n";
  }
  else {
    echo "MQTT face process started";
    [$is_approved,$from_turnstile,$turnstile_id] = $obj->verifyuserFromTurnStile($topic,$msg);

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
      $newmqtt->publish($publishtopic,$payload, 0, false);
      $newmqtt->close();
    }
    echo "MQTT face process finished";
  }
}

?>
