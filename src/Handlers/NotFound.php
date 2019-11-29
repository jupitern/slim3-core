<?php

namespace Jupitern\Slim3\Handlers;
use Jupitern\Slim3\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class NotFound extends \Slim\Handlers\NotFound
{

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface      $response
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \ReflectionException
	 */
	public function __invoke(Request $request, Response $response)
	{
		$app = app();

		if ($app->isConsole()) {
			return $response->write("Error: request does not match any command::method or mandatory params are not properly set\n");
		}

        if ($this->determineContentType($request) == 'application/json') {
		    return app()->error("URI '".$request->getUri()->getPath()."' not found", 404);
        }

		$resp = $app->resolve('view')->render('http::error', [
		    'code' => 404,
            'message' => "uri {$request->getUri()->getPath()} not found",
        ]);
        $response = $response->withStatus(404)->write($resp);

		return $response;
	}



}