<?php

namespace App\ServiceProviders;

use Jupitern\Slim3\Filesystem\Filesystem as FilesystemFilesystem;
use Jupitern\Slim3\ServiceProviders\ProviderInterface;
use Jupitern\Slim3\Filesystem\Filesystem as ExtendedFilesystem;
use Jupitern\Slim3\Filesystem\S3\AsyncAwsS3Adapter;
use Jupitern\Slim3\Filesystem\S3\ClientFactory;

class Filesystem implements ProviderInterface
{
    public static function register(string $serviceName, array $settings = [])
    {
        app()->getContainer()[$serviceName] = function ($c) use ($settings) {
            return function ($configsOverride = []) use ($settings) {

                $configs = array_merge($settings, $configsOverride);

                $filesystem = null;
                switch ($configs['driver']) {
                    case 'local':
                        $filesystem = self::createLocal($configs);
                        break;

                    case 'ftp':
                        $filesystem = self::createFtp($configs);
                        break;

                    case 's3Async':
                        $filesystem = self::createS3Async($configs);
                        break;

                    default:
                        throw new \Exception("filesystem driver {$configs['driver']} not found");
                        break;
                }

                return $filesystem;
            };
        };
    }

    private static function createLocal($configs)
    {
        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($configs['root']);

        return new ExtendedFilesystem($adapter, [], null);
    }

    private static function createFtp($configs)
    {
        $ftpOptions = \League\Flysystem\Ftp\FtpConnectionOptions::fromArray($configs);
        $adapter = new \League\Flysystem\Ftp\FtpAdapter($ftpOptions);

        return new ExtendedFilesystem($adapter, [], null);
    }
    
    private static function createS3Async($settings)
    {
        $client = ClientFactory::create($settings);

        $adapter = new AsyncAwsS3Adapter($client, $settings['bucket'], $settings['prefix'] ?? '', null, null);

        return new ExtendedFilesystem($adapter, [], null);
    }
}
