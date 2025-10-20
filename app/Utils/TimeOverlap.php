<?php

namespace App\Utils;

class TimeOverlap
{
    public static function hasConflict(string $startA, string $endA, string $startB, string $endB): bool
    {
        return ($startA < $endB) && ($startB < $endA);
    }
}
