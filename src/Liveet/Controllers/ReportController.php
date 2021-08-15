<?php

namespace Liveet\Controllers;

use Liveet\Models\OrganiserModel;
use Liveet\Models\OrganiserStaffModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\JSON;

class ReportController extends HelperController
{

    /** Admin Report */

    public function getOrganiserSummary(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminReportPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["organiser_id"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];

        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        return $this->getDashboardByConditions($request, $response, new OrganiserModel(), $conditions);
    }

    public function getOrganiserTimelySummary(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkAdminReportPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $expectedRouteParams = ["organiser_id", "from", "to", "interval"];
        $routeParams = $this->getRouteParams($request);

        $conditions = [];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $model = new OrganiserModel();
        $data = [];

        $interval = $conditions["interval"] ?? 86400;
        $from = $conditions["from"] ?? strtotime(date('Y-m-01 00:00:00'));
        $to = $conditions["to"] ?? strtotime(date("Y-m-t"));

        unset($conditions["interval"]);
        unset($conditions["from"]);
        unset($conditions["to"]);

        $newCond = [];
        foreach ($conditions as $key => $value) {
            $newCond[] = [$key, "=", $value];
        }

        $diff = $to - $from;
        $start = $from;
        $end = $start + $interval;

        do {
            $newCconditions = [["created_at", ">=", $start], ["created_at", "<=", $end], ...$newCond];

            $data[] = $model->getDashboard($newCconditions, [])["data"];

            $start = $end;
            $end += $interval;
        } while ($start < $to);

        $json = new JSON();

        $payload = array("successMessage" => "Report request success", "statusCode" => 200, "data" => $data);

        return $json->withJsonResponse($response, $payload);
    }

    /** Organiser */

    public function getOrganiserSelfTimelySummary(Request $request, ResponseInterface $response): ResponseInterface
    {
        $permissonResponse = $this->checkOrganiserReportPermission($request, $response);
        if ($permissonResponse != null) {
            return $permissonResponse;
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $organiser_id = $authDetails["organiser_id"];

        $expectedRouteParams = ["from", "to", "interval"];
        $routeParams = $this->getRouteParams($request);

        $conditions = ["organiser_id" => $organiser_id];
        foreach ($routeParams as $key => $value) {
            if (in_array($key, $expectedRouteParams) && $value != "-") {
                $conditions[$key] = $value;
            }
        }

        $model = new OrganiserModel();
        $data = [];

        $interval = $conditions["interval"] ?? 86400;
        $from = $conditions["from"] ?? strtotime(date('Y-m-01 00:00:00'));
        $to = $conditions["to"] ?? strtotime(date("Y-m-t"));

        unset($conditions["interval"]);
        unset($conditions["from"]);
        unset($conditions["to"]);

        $newCond = [];
        foreach ($conditions as $key => $value) {
            $newCond[] = [$key, "=", $value];
        }

        $start = $from;
        $end = $start + $interval;

        do {
            $newCconditions = [["created_at", ">=", $start], ["created_at", "<=", $end], ...$newCond];

            $data[] = $model->getDashboard($newCconditions, [])["data"];

            $start = $end;
            $end += $interval;
        } while ($start < $to);

        $json = new JSON();

        $payload = array("successMessage" => "Report request success", "statusCode" => 200, "data" => $data);

        return $json->withJsonResponse($response, $payload);
    }
}
