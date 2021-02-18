<?php

namespace Liveet\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Liveet\Models\LocationModel;

class TestController extends BaseController
{
    public function createManyBusLocations(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        /**
         * []
         */
        return (new BaseController)->createMany($request, $response, (new LocationModel()), [
            'busID', 'lat', 'lng', "issuerID", "issuerName", "time"
        ], [], ["organizationID" => $id]);
    }

    public function getSelfBusLocations(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        return (new BaseController)->getByDateWithConditions($request, $response, new LocationModel(), ["organizationID" => $id]);
    }

    public function getSelfBusLocationsByIssuerID(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        ['issuerID' => $issuerID, 'error' => $error] = $this->getRouteParams($request, ["issuerID"]);

        return (new BaseController)->getByDateWithConditions($request, $response, new LocationModel(), ["organizationID" => $id, "issuerID" => $issuerID]);
    }

    public function getSelfBusLocationsByBusID(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        ['busID' => $busID, 'error' => $error] = $this->getRouteParams($request, ["busID"]);

        return (new BaseController)->getByDateWithConditions($request, $response, new LocationModel(), ["organizationID" => $id, "busID" => $busID]);
    }

    public function getSelfBusLocationsByOptions(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        ['busID' => $busID, "issuerID" => $issuerID, "from" => $from, "to" => $to, 'error' => $error] = $this->getRouteParams($request, ["busID", "issuerID", "from", "to"]);

        if (isset($from) and $from == "-") {
            // $from = (int)(date("U")) - 86400;
            $options = [ "groupby" => ["busID"], "raw"=>"max(time) as time"];
        }

        // $to = (isset($to) and $to != "-")  ? $to : date("U");

        $conditions =  ["organizationID" => $id];
        $override = ["from" => $from, "to" => $to];

        if ($issuerID != "-") {
            $conditions["issuerID"] = $issuerID;
        }

        if ($busID != "-") {
            $conditions["busID"] = $busID;
        }

        return (new BaseController)->getByDateWithConditions($request, $response, new LocationModel(), $conditions, null, $override, $options);
    }

    public function getIssuers(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        return (new BaseController)->getAll($request, $response, new LocationModel(), ["issuerName", "issuerID"], ["organizationID" => $id], ["distinct" => true]);
    }
}
