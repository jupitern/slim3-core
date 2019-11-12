<?php

namespace Jupitern\Slim3\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class PhpError extends \Slim\Handlers\PhpError
{

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface      $response
	 * @param \Throwable                               $error
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \ReflectionException
	 * @throws \Throwable
	 */
	public function __invoke(Request $request, Response $response, \Throwable $error)
    {
        $app = app();
        $container = $app->getContainer();

        // Log the message
        $msg = $error->getMessage() .PHP_EOL. "line ". $error->getLine() ." on ". $error->getFile();
        $app->resolve(LoggerInterface::class)->error($msg,[
            "server"  => gethostname(),
            "user"    => array_key_exists('user', $container) ? [
                "id" => $container["user"]->id,
                "username" => $container["user"]->username,
            ] : "null",
            "trace"   => array_slice($error->getTrace(), 0, 5),
            "request" => [
                "query"  => $request->getQueryParams(),
                "body"   => $request->getBody(),
                "server" => array_intersect_key($request->getServerParams(), array_flip(["HTTP_HOST", "SERVER_ADDR", "REMOTE_ADDR", "SERVER_PROTOCOL", "HTTP_CONTENT_LENGTH", "HTTP_USER_AGENT", "REQUEST_URI", "CONTENT_TYPE", "REQUEST_TIME_FLOAT"]))
            ],
        ]);

        if (app()->has('slashtrace') && ($app->isConsole() || $this->displayErrorDetails)) {
            app()->resolve('slashtrace')->register();
            http_response_code(500);
            throw $error;
        }

        if (!$this->displayErrorDetails) {
            return app()->error($error->getMessage(), 500);
        }

        return parent::__invoke($request, $response, $error);
    }

}