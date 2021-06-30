<?php

namespace Jupitern\Slim3\Filesystem\S3;

use AsyncAws\S3\S3Client;
use League\Flysystem\AsyncAwsS3\VisibilityConverter;
use League\MimeTypeDetection\MimeTypeDetector;

class AsyncAwsS3Adapter extends \League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter
{
    protected $client;
    protected $bucket;
    protected $prefix;

    /**
     * * Important to validade if construct changes during package upgrades.
     *
     * @param S3Client|SimpleS3Client $client
     */
    public function __construct(
        S3Client $client,
        string $bucket,
        string $prefix = '',
        VisibilityConverter $visibility = null,
        MimeTypeDetector $mimeTypeDetector = null
    ) {
        parent::__construct($client, $bucket, $prefix, $visibility, $mimeTypeDetector);

        $this->client = $client;
        $this->bucket = $bucket;
        $this->prefix = $prefix;
    }

    public function bulkDelete(array $files): bool
    {
        $deletableObjects = [];

        foreach ($files as $filepath) {
            $deletableObjects[] = [
                "Key" => empty($this->prefix) ? $filepath : rtrim($this->prefix, '/') . '/' . ltrim($filepath, '/'),
            ];
        }

        $this->client->deleteObjects([
            "Bucket" => $this->bucket,
            "Delete" => [
                "Objects" => $deletableObjects,
                "Quiet"   => true,
            ],
        ]);

        return true;
    }
}
