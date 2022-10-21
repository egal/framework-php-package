<?php

declare(strict_types=1);

namespace Egal\ModelS3FileStoring;

use Aws\S3\S3ClientInterface;
use Egal\ModelFileStoring\FileStoring;

/**
 * @mixin \Egal\Model\Model
 * @property string[] $contentNames
 * @property \Illuminate\Filesystem\FilesystemAdapter $disk
 */
trait S3FileStoring
{

    use FileStoring;

    private S3ClientInterface $client;

    public function initializeS3FileStoring(): void
    {
        $this->initializeFileStoring();
        $this->client = $this->disk->getClient();
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

        return ['message' => 'Uploaded!'];
    }

    public static function actionCompleteMultipartUpload(string $path, string $uploadId): array
    {
        $file = new static();
        $file->client->completeMultipartUpload([
            'Bucket' => $file->getBucketName(),
            'Key' => $path,
            'UploadId' => $uploadId,
            'MultipartUpload' => ['Parts' => $file->getUploadedParts($path, $uploadId)->toArray()],
        ]);
        $file->disk->setVisibility($path, $file->getVisibility());

        return ['path' => $path];
    }

}
