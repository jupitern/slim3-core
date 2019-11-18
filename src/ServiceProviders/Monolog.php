<?php

namespace Jupitern\Slim3\ServiceProviders;
use Jupitern\Slim3\ServiceProviders\ProviderInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\WebProcessor;
use Monolog\Handler\SISHandler;

class Monolog implements ProviderInterface
{

	public static function register($serviceName, array $settings = [])
    {
        $monolog = new Logger($serviceName);
        $formatter = new LineFormatter(null, null, true);
        $formatter->includeStacktraces(false);

        foreach ($settings as $logger) {
            if ($logger['type'] == 'sis' && (bool)$logger['enabled']) {
                $handler = new SISHandler($logger['host'], $logger['apiKey'], $logger['level']);
                $monolog->pushHandler($handler);
                $monolog->pushProcessor(new WebProcessor());
            }
            elseif ($logger['type'] == 'file' && (bool)$logger['enabled']) {
                $handler = new StreamHandler($logger['path'], $logger['level']);
                $handler->setFormatter($formatter);
                $monolog->pushHandler($handler);
            }
        }

		app()->getContainer()[LoggerInterface::class] = $monolog;
    }

}