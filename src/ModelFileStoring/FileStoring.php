<?php

declare(strict_types=1);

namespace Egal\ModelFileStoring;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin \Egal\Model\Model
 * @property string[] $contentNames
 */
trait FileStoring
{

    private Filesystem $disk;

    public function initializeFileStoring(): void
    {
        foreach ($this->getContentNames() as $contentName) {
            $this->append(Str::snake($this->getContentUrlPropertyName($contentName)));
            $this->makeHidden(Str::snake($this->getContentPathPropertyName($contentName)));
            $this->fillable(array_merge(
                $this->getFillable(),
                [Str::snake($this->getContentPathPropertyName($contentName))]
            ));
        }

        $this->disk = Storage::disk($this->getDiskName());
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

    public static function actionUpload(string $fileBasename, string $contents): array
    {
        $file = new static();
        $path = $file->generatePath($fileBasename);
        $file->disk->put($path, $contents, $file->getVisibility());

        return ['path' => $path];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        if ($this->isNeedMutateUrlFields() && str_ends_with($key, $this->getContentUrlPropertyNamePostfix())) {
            $contentName = $this->getContentName($key);

            if ($this->isContentExists($contentName)) {
                return $this->getContentUrl($contentName);
            }
        }

        return parent::mutateAttribute($key, $value);
    }

    protected function getContentUrl(string $contentName): string
    {
        return $this->disk->url($this->getContentPath($contentName));
    }

    /**
     * @return string[]
     */
    private function getContentNames(): array
    {
        return $this->contentNames ?? [];
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

    private function getDiskName(): string
    {
        return $this->diskName ?? config('filesystems.default');
    }

    private function getUploadedParts(string $key, string $uploadId): Collection
    {
        return $this->listPartsPage($key, $uploadId, 0);
    }

    private function listPartsPage(
        string $key,
        string $uploadId,
        int $partNumber,
        ?Collection $parts = null
    ): Collection {
        $parts ??= collect();

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
