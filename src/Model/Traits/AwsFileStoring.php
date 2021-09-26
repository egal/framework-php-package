<?php

namespace Egal\Model\Traits;

use Egal\Model\Model;
use Egal\ModelS3FileStoring\S3FileStoring;

/**
 * @mixin Model
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
