<?php

namespace BUS_LOCATOR\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use BUS_LOCATOR\Models\LocationModel;

class LocationController extends BaseController
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
}
