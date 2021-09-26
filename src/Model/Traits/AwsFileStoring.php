<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\ModelS3FileStoring\S3FileStoring;

/**
 * @mixin \Egal\Model\Model
 * @property string[] $contentNames
 * @deprecated since v2.0.0, use {@see FileStoring} or {@see S3FileStoring}.
 */
trait AwsFileStoring
{

    use S3FileStoring;

    private function getDiskName(): string
    {
        return $this->diskName ?? 's3';
    }

}
