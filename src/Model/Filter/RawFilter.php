<?php

declare(strict_types=1);

namespace Egal\Model\Filter;

use Illuminate\Support\Str;

class RawFilter
{

    public static function getRaw(array $arrayFilter = [], array $databaseProperties = []): ?string
    {
        $rawFilter = null;

        if ($arrayFilter !== []) {
            $rawFilter = json_encode($arrayFilter);
            $table = Str::snake(Str::pluralStudly(class_basename(static::class)));

            $rawFilter = str_replace('"', "'", $rawFilter);

            foreach ($databaseProperties as $databaseProperty) {
                if (!str_contains($rawFilter, $databaseProperty)) continue;

                $rawFilter = str_replace(
                    "'" . $databaseProperty . "'",
                    '"' . $table . '"' . '.' . '"' . $databaseProperty . '"',
                    $rawFilter,
                );
            }

            $rawFilter = str_replace("',", ' ', $rawFilter);
            $rawFilter = str_replace(",'", ' ', $rawFilter);
            $rawFilter = str_replace('[', '(', $rawFilter);
            $rawFilter = str_replace(']', ')', $rawFilter);
        }

        return $rawFilter;
    }

}
