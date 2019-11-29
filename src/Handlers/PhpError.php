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
        $app       = app();
        $container = $app->getContainer();

        // Log the message
        $errorCode = 500;
        $msg = $error->getMessage() .PHP_EOL. $error->getFile() .PHP_EOL. "on line ". $error->getLine();
        $messages = app()->isConsole() || $this->displayErrorDetails ?
            array_slice(preg_split('/\r\n|\r|\n/', $error->getTraceAsString()), 0, 10) : [];

        $errorMsg = ["code" => $errorCode, "error" => $msg, "messages" => $messages];

        $app->resolve(LoggerInterface::class)->error($msg, [
            "error"     => $errorMsg['error'],
            "messages"  => implode(PHP_EOL, $errorMsg['messages']),
            "server"    => gethostname(),
            "user"      => array_key_exists('user', $container) ? [
                "id"    => $container["user"]->id,
                "username" => $container["user"]->username,
            ] : "null",
            "request" => [
                "query"  => $request->getQueryParams(),
                "body"   => $request->getBody(),
                "server" => array_intersect_key($request->getServerParams(), array_flip(["HTTP_HOST", "SERVER_ADDR", "REMOTE_ADDR", "SERVER_PROTOCOL", "HTTP_CONTENT_LENGTH", "HTTP_USER_AGENT", "REQUEST_URI", "CONTENT_TYPE", "REQUEST_TIME_FLOAT"]))
            ]
        ]);

        if (app()->has('slashtrace') && (app()->isConsole() || $this->displayErrorDetails)) {
            app()->resolve('slashtrace')->register();
            http_response_code($errorCode);
            throw $error;
        }

        if (app()->isConsole() || !$this->displayErrorDetails) {
            return app()->error($errorMsg, $errorCode);
        }

        return parent::__invoke($request, $response, $error);
    }

}