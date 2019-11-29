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
        $errorCode = $exception instanceof NestedValidationException ? 422 : 500;
        $msg = $exception->getMessage() .PHP_EOL. $exception->getFile() .PHP_EOL. "on line ". $exception->getLine();
        $messages = $errorCode == 422 ? $exception->getMessages() :
            (app()->isConsole() || $this->displayErrorDetails ? array_slice(preg_split('/\r\n|\r|\n/', $exception->getTraceAsString()), 0, 10) : []);

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
            throw $exception;
        }

        if (app()->isConsole() || !$this->displayErrorDetails) {
            return app()->error($errorMsg, $errorCode);
        }

        return parent::__invoke($request, $response, $exception);
    }

}