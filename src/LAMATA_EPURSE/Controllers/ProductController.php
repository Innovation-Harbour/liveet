<?php

namespace LAMATA_EPURSE\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use LAMATA_EPURSE\Models\ProductModel;

class ProductController extends BaseController
{

    public function getProducts(Request $request, ResponseInterface $response): ResponseInterface
    {
        return (new BaseController())->getAll($request, $response, new ProductModel());
    }
}
