<?php

namespace LAMATA_EPURSE\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use LAMATA_EPURSE\Models\TransactionModel;

class TransactionController extends BaseController
{
    public function createManyBusTransaction(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        /**
         * []
         */
        return (new BaseController)->createMany($request, $response, new TransactionModel(), [
            'entryPoint', "entryTime", 'exitPoint', "exitTime", "cardType", "cardSerial", "busID", "amount"
        ], [], ["userID" => $id]);
    }

    public function getSelfBusTransaction(Request $request, ResponseInterface $response): ResponseInterface
    {
        ["id" => $id] = self::getTokenInputsFromRequest($request);

        return (new BaseController)->getByDateWithConditions($request, $response, new TransactionModel(), ["userID" => $id]);
    }
}
