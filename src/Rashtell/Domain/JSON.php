<?php

namespace Rashtell\Domain;

use Rashtell\Http\Response;

class JSON
{

	function unArray($str)
	{
		if (substr($str, 0, 1) == "[") {
			$newStr = json_decode($str);
			$newArr = $newStr[0];
			$newArr = json_encode($newArr);
			return $newArr;
		} else {
			return $str;
		}
	}

	public function jsonFormat($json)
	{
		$type = gettype($json);
		if ($type != "array") {
			$json = (urldecode($json) != null) ? urldecode($json) : $json;
			$json = $this->cleanJson($json);
		} else {
			$json = json_encode($json);
		}
		if ($this->isJson($json)) {
			return json_decode($json);
		} else {
			return NULL;
		}
	}

	function isJson($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}


	/**
	 * @param AppMangaer\Domain\Response $response, 
	 * @param array $payload = 
	 * 							[
	 * 								"errorStatus" => "0",
	 * 								"errorMessage" => "", 
	 *	 							"successMessage" => "", 
	 * 								"statusCode" => 200, 
	 * 								"data" => [],
	 * 								"token" => null
	 * 							]
	 */
	public function withJsonResponse($response, array $payload = ["errorStatus" => 0, "errorMessage" => "", "successMessage" => "", "statusCode" => 200, "data" => null])
	{
		$response = new Response();

		extract($payload);

		$errorStatus = isset($errorStatus) ? $errorStatus : 0;
		$errorMessage = isset($errorMessage) ? $errorMessage : "";
		$successMessage = isset($successMessage) ? $successMessage : "";
		$statusCode = isset($statusCode) ? $statusCode : 200;
		$data = isset($data) ? $data : null;

		$payload = isset($token) ?
			array("success" => array("message" => $successMessage, "code" => $statusCode), "content" =>  $data ? array("data" => $data) : null, "token" => $token)
			: ($statusCode < 400 ?
				array("success" => array("message" => $successMessage, "code" => $statusCode), "content" => $data ? array("data" => $data) : null) :
				array("error" => array("message" => $errorMessage, "code" => $errorStatus)));

		return $response->withStatus(200)
			->withHeader('Content-Type', 'application/json')
			->withHeader('Access-Control-Allow-Origin', '*')
			->withHeader('Access-Control-Allow-Headers', array('Content-Type', 'X-Requested-With', 'Authorization', 'PI'))
			->withHeader('Access-Control-Allow-Methods', array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'))
			->withJson($payload);
	}

	function cleanException($ex)
	{
		$poison = array("'", "\\", "\"");
		$exf = str_replace($poison, "", $ex);
		return $exf;
	}

	function cleanJson($json)
	{
		$start = array();
		$endIndex = array();
		$count = 0;
		$json = trim($json);
		while ($count < strlen($json)) {
			if (substr($json, $count, 1) == "{") {
				array_push($start, $count);
			} else if (substr($json, $count, 1) == "}") {
				array_push($endIndex, $count);
			} else {
			}
			$count++;
		}
		return substr($json, $start[0], $endIndex[count($endIndex) - 1] - $start[0] + 1);
	}
}
