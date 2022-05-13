<?php

declare(strict_types=1);

namespace Egal\ModelS3FileStoring;

use Aws\S3\S3ClientInterface;
use Egal\Model\Exceptions\ValidateException;
use Egal\ModelFileStoring\FileStoring;
use Illuminate\Support\Facades\Validator;

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
        $this->client = $this->disk->getDriver()->getAdapter()->getClient();
    }

    public static function actionCreateMultipartUpload(array $attributes): array
    {
        $validator = Validator::make($attributes, [
            'file_basename' => 'required|string'
        ]);

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());
            throw $exception;
        }

        $file = new static();
        $path = $file->generatePath($attributes['file_basename']);
        $result = $file->client->createMultipartUpload([
            'Bucket' => $file->getBucketName(),
            'Key' => $path,
        ]);

        return [
            'upload_id' => $result['UploadId'],
            'path' => $result['Key'],
        ];
    }

    public static function actionUploadPart(array $attributes): array
    {
        $validator = Validator::make($attributes, [
            'upload_id' => 'required|string',
            'path' => 'required|string',
            'part_number' => 'required|int',
            'contents' => 'required|string'
        ]);

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());
            throw $exception;
        }

        $file = new static();
        $file->client->uploadPart([
            'Bucket' => $file->getBucketName(),
            'Key' => $attributes['path'],
            'UploadId' => $attributes['upload_id'],
            'PartNumber' => $attributes['part_number'],
            'Body' => $attributes['contents'],
        ]);

        return ['message' => 'Uploaded!'];
    }

    public static function actionCompleteMultipartUpload(array $attributes): array
    {
        $validator = Validator::make($attributes, [
            'path' => 'required|string',
            'upload_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());
            throw $exception;
        }

        $file = new static();
        $path = $attributes['path'];
        $uploadId = $attributes['upload_id'];
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
