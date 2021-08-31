<?php

namespace Egal\Model\Traits;

use Aws\S3\S3Client;
use Egal\Model\Model;
use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin Model
 * @property string[] $contentNames
 */
trait AwsFileStoring
{

    private FilesystemAdapter $disk;
    private S3Client $client;

    /**
     * @return string[]
     */
    private function getContentNames(): array
    {
        return $this->contentNames ?? [];
    }

    public function initializeAwsFileStoring()
    {
        foreach ($this->getContentNames() as $contentName) {
            $this->append(Str::snake($this->getContentUrlPropertyName($contentName)));
            $this->makeHidden(Str::snake($this->getContentPathPropertyName($contentName)));
            $this->fillable(array_merge(
                $this->getFillable(),
                [Str::snake($this->getContentPathPropertyName($contentName))]
            ));
        }

        $this->disk = Storage::disk($this->getDiskName() ?? 's3');
        $this->client = $this->disk->getDriver()->getAdapter()->getClient();
    }

    public static function actionUpload(string $fileBasename, string $contents): array
    {
        $file = new static();
        $path = $file->generatePath($fileBasename);
        $file->disk->put($path, $contents, $file->getVisibility());

        return [
            'path' => $path
        ];
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

    private function generatePath(string $fileBasename): string
    {
        $dir = config('app.service_name')
            . DIRECTORY_SEPARATOR
            . get_class_short_name(static::class);

        $fileName = str_slug(
            date('Y_m_d_His')
            . '_'
            . pathinfo($fileBasename, PATHINFO_FILENAME)
        );

        $fileExtension = pathinfo($fileBasename, PATHINFO_EXTENSION);
        $path = $dir . DIRECTORY_SEPARATOR . $fileName;

        if ($fileExtension !== '') {
            $path .= '.' . $fileExtension;
        }

        return $path;
    }

    private function getBucketName(): string
    {
        return config('filesystems.disks.' . $this->getDiskName() . '.bucket');
    }

    private function getDiskName(): ?string
    {
        return $this->diskName ?? 's3';
    }

    private function getUploadedParts(string $key, string $uploadId): Collection
    {
        return $this->listPartsPage($key, $uploadId, 0);
    }

    private function listPartsPage(string $key, string $uploadId, int $partNumber, Collection $parts = null): Collection
    {
        $parts = $parts ?? collect();

        $results = $this->client->listParts([
            'Bucket' => $this->getBucketName(),
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumberMarker' => $partNumber,
        ]);

        if ($results['Parts']) {
            $parts = $parts->concat($results['Parts']);
            if ($results['IsTruncated']) {
                $results = $this->listPartsPage($key, $uploadId, $results['NextPartNumberMarker'], $parts);
                $parts = $parts->concat($results['Parts']);
            }
        }

        return $parts;
    }

    private function getVisibility(): string
    {
        return config('filesystems.disks.' . $this->getDiskName() . '.visibility');
    }

    protected function mutateAttribute($key, $value)
    {
        if (
            $this->isNeedMutateUrlFields()
            && str_ends_with($key, $this->getContentUrlPropertyNamePostfix())
            && $this->isContentExists($contentName = $this->getContentName($key))
        ) {
            return $this->disk->url($this->getContentPath($contentName));
        }

        return parent::mutateAttribute($key, $value);
    }

    public function getContentName(string $contentNameOrPathOrUrl): string
    {
        if (str_ends_with($contentNameOrPathOrUrl, $this->getContentPathPropertyNamePostfix())) {
            $contentName = str_replace($this->getContentPathPropertyNamePostfix(), '', $contentNameOrPathOrUrl);
        } elseif (str_ends_with($contentNameOrPathOrUrl, $this->getContentUrlPropertyNamePostfix())) {
            $contentName = str_replace($this->getContentUrlPropertyNamePostfix(), '', $contentNameOrPathOrUrl);
        } else {
            throw new Exception('Content does not exists!');
        }

        $this->isContentExistsOrFail($contentName);

        return $contentName;
    }

    private function getContentPathPropertyNamePostfix(): string
    {
        return $this->contentPathPropertyNamePostfix ?? '_path';
    }

    private function isNeedMutateUrlFields(): bool
    {
        return $this->needMutateUrlFields ?? true;
    }

    private function getContentPathPropertyName(string $contentName): string
    {
        return $contentName . $this->getContentPathPropertyNamePostfix();
    }

    private function getContentUrlPropertyNamePostfix(): string
    {
        return $this->contentUrlPropertyNamePostfix ?? '_url';
    }

    private function getContentUrlPropertyName(string $contentName): string
    {
        return $contentName . $this->getContentUrlPropertyNamePostfix();
    }

    private function isContentExists(string $contentName): bool
    {
        return in_array($contentName, $this->getContentNames());
    }

    private function isContentExistsOrFail(string $contentName): bool
    {
        if (!$this->isContentExists($contentName)) {
            throw new Exception('Content does not exists!');
        }

        return true;
    }

    private function getContentPath(string $contentName): string
    {
        return $this->getAttribute($this->getContentPathPropertyName($contentName));
    }

}
