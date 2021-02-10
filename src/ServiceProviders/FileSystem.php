<?php

namespace Jupitern\Slim3\ServiceProviders;


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
                        $adapter    = new \League\Flysystem\Adapter\Local($configs['root']);
                        $filesystem = new \League\Flysystem\Filesystem($adapter);
                        break;

                    case 'ftp':
                        $adapter    = new \League\Flysystem\Adapter\Ftp($configs);
                        $filesystem = new \League\Flysystem\Filesystem($adapter);
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

                        $client     = new \Aws\S3\S3Client($s3Configs);
                        $adapter    = new \League\Flysystem\AwsS3v3\AwsS3Adapter($client, $configs["bucket"], $container);
                        $filesystem = new \League\Flysystem\Filesystem($adapter, ["visibility" => "public"]);
                        break;

                    case 's3Async':
                        $container = $configs["containerPrefix"] . "/" . $configs["container"];

                        $client = new \AsyncAws\S3\S3Client([
                            'endpoint' => $configs["endpoint"],
                            'accessKeyId' => $configs["key"],
                            'accessKeySecret' => $configs["secret"],
                            'pathStyleEndpoint' => true,
                        ]);

                        $adapter = new \League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter($client, 'social-storage-staging');
                        $filesystem = new \League\Flysystem\Filesystem($adapter);

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
