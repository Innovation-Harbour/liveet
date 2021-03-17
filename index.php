<?php

require "bootstrap.php";

use Liveet\Domain\Constants;
use Rashtell\Domain\JSON;
use Slim\Factory\AppFactory;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use Slim\Middleware\ContentLengthMiddleware;
use Liveet\Middlewares\CORSMiddleware;
use Liveet\Middlewares\LogMiddleware;
use Liveet\Middlewares\ToJsonMiddleware;
use Slim\Exception\HttpNotFoundException;

// error_reporting(0);

$app = AppFactory::create();

$app->options("/{routes:.+}", function ($request, $response, $args) {
    return $response;
});

$app->addMiddleware(new CORSMiddleware());

// Parse json, form data and xml
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// $app->addMiddleware(new LogMiddleware());
// $app->addMiddleware(new ToJsonMiddleware());
$app->add(new ContentLengthMiddleware());

// $basePath = Constants::DEVELOPMENT_BASE_PATH;

// if ($_SERVER["HTTP_HOST"] == Constants::PRODUCTION_HOST) {
//     $basePath = Constants::PRODUCTION_BASE_PATH;
// }


$app->group(
    $basePath,
    function (RouteCollectorProxy $appGroup) {
        require "src/Liveet/Routes/index.php";
    }
);



/**
 * test
 */
$app->get($basePath . "/v1/test", function (Request $request, Response $response) {

    $data = array("testing /login" => "true");

    return $response
        ->withHeader("Content-Type", "application/json")
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", array("Content-Type", "X-Requested-With", "Authorization", "PI"))
        ->withHeader("Access-Control-Allow-Methods", array("GET", "POST", "PUT", "DELETE", "OPTIONS"))
        ->withStatus(200)
     ->getBody()->write("Hello World");
    //  ->withJson($data);
});


/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 */

$app->map(["GET", "POST", "PUT", "DELETE", "PATCH"], "/{routes:.+}", function ($request, $response) {

    // throw new HttpNotFoundException($request);

    $json = new JSON();
    // var_dump($request);
    return $json->withJsonResponse($response, ["errorMessage" => "404 Endpoint not found", "errorStatus" => 1, "statusCode" => 404, "data" => ["method" => $request->getMethod(), "url" => $request->getServerParams()["REQUEST_SCHEME"] . "://"
        . $request->getServerParams()["HTTP_HOST"] . $request->getServerParams()["REQUEST_URI"]]]);
});

// 
/**
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.
 * 
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */

// $app->addErrorMiddleware(true, true, true);

$app->run();
