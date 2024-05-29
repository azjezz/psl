<?php

declare(strict_types=1);

namespace Psl\DateTime;

/**
 * Represents the two eras in the Gregorian and Julian calendars as an enum.
 *
 * This enum distinguishes between the Anno Domini (AD) era, denoting years after the birth of Jesus Christ,
 * and the Before Christ (BC) era, denoting years before the birth of Jesus Christ. It provides a type-safe way
 * to represent and work with these two divisions of historical time.
 */
enum Era: string
{
    case AnnoDomini = 'AD';
    case BeforeChrist = 'BC';

    /**
     * Creates an Era instance based on the given year.
     *
     * @param int $year The year, positive for AD and negative for BC.
     *
     * @return Era Returns AnnoDomini for positive years and BeforeChrist for negative years.
     *
     * @pure
     */
    public static function fromYear(int $year): Era
    {
        return $year > 0 ? self::AnnoDomini : self::BeforeChrist;
    }

    /**
     * Toggles between AnnoDomini (AD) and BeforeChrist (BC).
     *
     * @return Era Returns BeforeChrist if the current instance is AnnoDomini, and vice versa.
     *
     * @psalm-mutation-free
     */
    public function toggle(): Era
    {
        return $this === self::AnnoDomini ? self::BeforeChrist : self::AnnoDomini;
    }
}
