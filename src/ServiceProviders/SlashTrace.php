<?php

namespace Jupitern\Slim3\ServiceProviders;
use Jupitern\Slim3\ServiceProviders\ProviderInterface;
use SlashTrace\SlashTrace as ST;
use SlashTrace\EventHandler\DebugHandler;

class SlashTrace implements ProviderInterface
{

	public static function register(string $serviceName, array $settings = [])
	{
	    $st = new ST();
        $st->addHandler(new DebugHandler());

        app()->getContainer()[$serviceName] = $st;
	}

}