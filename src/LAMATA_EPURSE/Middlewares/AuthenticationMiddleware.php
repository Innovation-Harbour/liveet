<?php

namespace LAMATA_EPURSE\Middlewares;

use LAMATA_EPURSE\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Rashtell\Domain\JSON;
use LAMATA_EPURSE\Models\BaseModel;
use Rashtell\Domain\CodeLibrary;

class AuthenticationMiddleware implements MiddlewareInterface
{

    function __construct($model)
    {
        $this->model = $model;
    }
    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        $path = preg_split("(\/v1.+)", $path)[0];

        $response = new Response();
        $json = new JSON();

        //Authentication exceptions
        $authExceptionsTotalMatch = [$path . '/v1/organizations/login/organization', $path . '/v1/organizations/logout/organization',];

        $authExceptionsPartialMatch = [];

        $uri = $request->getUri();

        $cdl = new CodeLibrary();

        if ($cdl->in_array_partial($authExceptionsPartialMatch, $uri->getPath()) or in_array($uri->getPath(), $authExceptionsTotalMatch)) {
            $response =
                $handler->handle($request);

            return $response;
        }

        $token = BaseController::getToken($request);

        if (!$token) {
            $request->withAttribute('isAuthenticated', false);

            return $json->withJsonResponse($response, ['statusCode' => 401, 'errorMessage' => 'Unauthorized user. Please login.', 'errorCode' => 1]);
        };

        ['isAuthenticated' => $isAuthenticated, 'error' => $error] = $this->model->authenticate($token);

        if (!$isAuthenticated) {
            $request->withAttribute('isAuthenticated', false);

            return $json->withJsonResponse($response, ['statusCode' => 401, 'errorMessage' => $error . '. Please login.', 'errorCode' => 1]);
        }

        $response = $handler->handle($request);

        return $response;
    }
}
