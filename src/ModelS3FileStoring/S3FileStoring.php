<?php

namespace Egal\ModelS3FileStoring;

use Aws\S3\S3Client;
use Egal\Model\Model;
use Egal\ModelFileStoring\FileStoring;

/**
 * @mixin Model
 * @property string[] $contentNames
 */
trait S3FileStoring
{

    use FileStoring;

    private S3Client $client;

    public function initializeFileStoring()
    {
        $this->client = $this->disk->getDriver()->getAdapter()->getClient();
    }

    public static function actionCreateMultipartUpload(string $fileBasename): array
    {
        $file = new static();
        $path = $file->generatePath($fileBasename);
        $result = $file->client->createMultipartUpload([
            'Bucket' => $file->getBucketName(),
            'Key' => $path,
        ]);

        return [
            'upload_id' => $result['UploadId'],
            'path' => $result['Key'],
        ];
    }

    public static function actionUploadPart(string $uploadId, string $path, int $partNumber, string $contents): array
    {
        $file = new static();
        $file->client->uploadPart([
            'Bucket' => $file->getBucketName(),
            'Key' => $path,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
            'Body' => $contents,
        ]);

        return [
            'message' => 'Uploaded!'
        ];
    }

    public static function actionCompleteMultipartUpload(string $path, string $uploadId): array
    {
        $file = new static();
        $file->client->completeMultipartUpload([
            'Bucket' => $file->getBucketName(),
            'Key' => $path,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => $file->getUploadedParts($path, $uploadId)->toArray()
            ],
        ]);
        $file->disk->setVisibility($path, $file->getVisibility());

        return [
            'path' => $path
        ];
    }

}
