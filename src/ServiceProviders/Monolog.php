<?php

namespace Jupitern\Slim3\ServiceProviders;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\WebProcessor;
use Monolog\Handler\SISHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\DataDogHandler;

class Monolog implements ProviderInterface
{

    public static function register($serviceName, array $settings = [])
    {
        $monolog = new Logger($serviceName);
        $formatter = new LineFormatter(null, null, true);
        $formatter->includeStacktraces(false);

        foreach ($settings as $logger) {

            if ($logger['type'] == 'file' && (bool)$logger['enabled']) {
                $formatter = new LineFormatter(null, null, true);
                $formatter->includeStacktraces(true);

                $handler = new StreamHandler($logger['path'], $logger['level']);
                $handler->setFormatter($formatter);
                $monolog->pushHandler($handler);
            
            } elseif ($logger['type'] == 'sis' && (bool)$logger['enabled']) {
                $handler = new SISHandler($logger['host'], $logger['apiKey'], $logger['level']);
                $monolog->pushHandler($handler);
                $monolog->pushProcessor(new WebProcessor());
            
            } elseif ($logger['type'] == 'papertrail' && (bool)$logger['enabled']) {
                $output = "%channel%.%level_name%: %message%";
                $formatter = new LineFormatter($output);
                
                $handler = new SyslogUdpHandler($logger['host'], $logger['port']);
                $handler->setFormatter($formatter);
                $monolog->pushHandler($handler);

            } elseif ($logger['type'] == 'datadog' && (bool)$logger['enabled']) {
                $handler = new DataDogHandler($logger['host'], $logger['apiKey'], $logger['appKey'], $logger['level']);
                $monolog->pushHandler($handler);
            
            }
        }

	    app()->getContainer()['logger'] = $monolog;
    }

}
