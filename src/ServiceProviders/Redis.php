<?php

namespace Jupitern\Slim3\ServiceProviders;
use Jupitern\Slim3\ServiceProviders\ProviderInterface;
use Predis\Client;
use Lib\Utils\Redis as RedisClient;

class Redis implements ProviderInterface
{

    public static function register($serviceName, array $settings = [])
    {
        app()->getContainer()[$serviceName] = function ($c) use($serviceName, $settings) {

            $con = new RedisClient(new Client($settings));

            return $con;
        };
    }

}