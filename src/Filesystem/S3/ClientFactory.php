<?php

namespace Jupitern\Slim3\Filesystem\S3;

class ClientFactory
{
    public static function create(array $settings): \AsyncAws\SimpleS3\SimpleS3Client
    {
        return new \AsyncAws\SimpleS3\SimpleS3Client([
            'endpoint'          => $settings['endpoint'],
            'accessKeyId'       => $settings['key'],
            'accessKeySecret'   => $settings['secret'],
            'region'            => $settings['region'],
            'pathStyleEndpoint' => true,
        ]);
    }
}
