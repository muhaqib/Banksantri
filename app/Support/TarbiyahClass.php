<?php

namespace App\Support;

class TarbiyahClass
{
    public const LEVELS = [
        '1 Ibtida',
        '2 Ibtida',
        '3 Ibtida',
        '1 Tsanawi',
        '2 Tsanawi',
        '3 Tsanawi',
    ];

    public static function next(?string $class): ?string
    {
        $index = array_search($class, self::LEVELS, true);

        if ($index === false) {
            return null;
        }

        return self::LEVELS[$index + 1] ?? null;
    }
}
