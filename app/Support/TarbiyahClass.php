<?php

namespace App\Support;

use App\Models\PondokClass;
use Illuminate\Support\Facades\Schema;

class TarbiyahClass
{
    public const DEFAULT_LEVELS = [
        '1 Ibtida',
        '2 Ibtida',
        '3 Ibtida',
        '1 Tsanawi',
        '2 Tsanawi',
        '3 Tsanawi',
    ];

    public const LEVELS = self::DEFAULT_LEVELS;

    public static function levels(bool $activeOnly = true): array
    {
        if (Schema::hasTable('pondok_classes')) {
            $query = PondokClass::query();

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            $levels = $query
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all();

            if ($levels) {
                return $levels;
            }
        }

        return self::DEFAULT_LEVELS;
    }

    public static function next(?string $class): ?string
    {
        $levels = self::levels();
        $index = array_search($class, $levels, true);

        if ($index === false) {
            return null;
        }

        return $levels[$index + 1] ?? null;
    }
}
