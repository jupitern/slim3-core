<?php

namespace Jupitern\Slim3\Handlers;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Exceptions\NestedValidationException;

final class PhpError extends \Slim\Handlers\PhpError
{

	/**
	 * @param Request       $request
	 * @param Response      $response
	 * @param \Throwable    $error
	 *
	 * @return Response
	 * @throws \ReflectionException
	 * @throws \Throwable
	 */
	public function __invoke(Request $request, Response $response, \Throwable $error)
    {
        $app = app();
        
        $userInfo = [];
        if ($app->has('user')) {
            $user = $app->resolve('user');
            $userInfo = [
                "id" => is_object($user) ? $user->id ?? $user->UserID ?? $user->userid ?? null : null,
                "username" => is_object($user) ? $user->username ?? $user->Username ?? null : null,
            ];
        }

        // Log the message
        $errorCode = 500;
        $errorMsg  = $error->getMessage() ." on ".  $error->getFile() ." line ". $error->getLine();
        $stackTrace = array_slice(preg_split('/\r\n|\r|\n/', $error->getTraceAsString()), 0, 10);
    
        if ($app->has('logger')) {
            $app->resolve('logger')->error($errorMsg, [
                "trace"     => $stackTrace,
                "user"      => $userInfo,
                "host"      => gethostname(),
                "request"   => array_intersect_key($request->getServerParams(), array_flip(["HTTP_HOST", "SERVER_ADDR", "REMOTE_ADDR", "SERVER_PROTOCOL", "HTTP_CONTENT_LENGTH", "HTTP_USER_AGENT", "REQUEST_URI", "CONTENT_TYPE", "REQUEST_TIME_FLOAT"])),
                // "query" => $request->getQueryParams(),
            ]);
        }

        $errorMsg = [
            "code" => $errorCode,
            "error" => $this->displayErrorDetails ? $errorMsg : "Error",
            "messages" => $this->displayErrorDetails ? $stackTrace : [],
        ];

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