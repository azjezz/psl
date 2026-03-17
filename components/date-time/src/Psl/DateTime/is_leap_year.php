<?php

declare(strict_types=1);

namespace Psl\DateTime;

/**
 * Check if the given year is a leap year.
 *
 * Returns true if the specified year is a leap year according to the Gregorian
 * calendar; otherwise, returns false.
 *
 * @return bool True if the year is a leap year, false otherwise.
 *
 * @pure
 */
function is_leap_year(int $year): bool
{
    return ($year % 4) === 0 && (($year % 100) !== 0 || ($year % 400) === 0);
}
