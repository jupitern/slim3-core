<?php

namespace Jupitern\Slim3\ServiceProviders;
use League\Flysystem\Filesystem as FlySystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;


class FileSystem implements ProviderInterface
{

    public static function register($serviceName, array $settings = [])
    {
        app()->getContainer()[$serviceName] = function ($c) use($settings) {
            return function ($configsOverride = []) use($settings) {
                
                $configs = array_merge($settings, $configsOverride);

                $filesystem = null;
                switch ($configs['driver']) {
                    case 'local':
                        $adapter    = new Local($configs['root']);
                        $filesystem = new FlySystem($adapter);
                        break;

                    case 'ftp':
                        $adapter    = new FtpAdapter($configs);
                        $filesystem = new FlySystem($adapter);
                        break;

                    case 's3':
                        $container = $configs["containerPrefix"] . "/" . $configs["container"];
                        $s3Configs = [
                            "endpoint"    => $configs["endpoint"],
                            "version"     => $configs["version"],
                            "credentials" => [
                                "key"    => $configs["key"],
                                "secret" => $configs["secret"]
                            ],
                            "region"      => $configs["region"],
                        ];

                        $client     = new S3Client($s3Configs);
                        $adapter    = new AwsS3Adapter($client, $configs["bucket"], $container);
                        $filesystem = new FlySystem($adapter, ["visibility" => "public"]);
                        break;

                    default:
                        throw new \Exception("filesystem driver {$configs['driver']} not found");
                        break;
                }
                return $filesystem;
            };
        };
    }
}
