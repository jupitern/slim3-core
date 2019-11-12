<?php

namespace Jupitern\Slim3\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Exceptions\NestedValidationException;

final class Error extends \Slim\Handlers\Error
{

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \Exception                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        $app       = app();
        $container = $app->getContainer();

        // Log the message
        $msg = $exception->getMessage() .PHP_EOL. "line ". $exception->getLine() ." on ". $exception->getFile();
        $app->resolve(LoggerInterface::class)->error($msg,[
            "server"  => gethostname(),
            "user"    => array_key_exists('user', $container) ? [
                "id" => $container["user"]->id,
                "username" => $container["user"]->username,
            ] : "null",
            "trace"   => array_slice($exception->getTrace(), 0, 5),
            "request" => [
                "query"  => $request->getQueryParams(),
                "body"   => $request->getBody(),
                "server" => array_intersect_key($request->getServerParams(), array_flip(["HTTP_HOST", "SERVER_ADDR", "REMOTE_ADDR", "SERVER_PROTOCOL", "HTTP_CONTENT_LENGTH", "HTTP_USER_AGENT", "REQUEST_URI", "CONTENT_TYPE", "REQUEST_TIME_FLOAT"]))
            ],
        ]);

        if (app()->has('slashtrace') && $app->isConsole()) {
            app()->resolve('slashtrace')->register();
            http_response_code(500);
            throw $exception;
        }

        if ($exception instanceof NestedValidationException) {
            return app()->error([
                "error" => "Input data validation failed",
                "reason" => $this->displayErrorDetails ? $exception->getMessages(): []
            ], 422);
        }

        if (!$this->displayErrorDetails) {
            return app()->error($exception->getMessage(), 500);
        }

        return parent::__invoke($request, $response, $exception);
    }

}