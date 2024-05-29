<?php

declare(strict_types=1);

namespace Psl\DateTime;

/**
 * Represents the days of the week as an enum.
 *
 * This enum provides a type-safe way to work with weekdays, offering methods to
 * get the previous and next day. Each case in the enum corresponds to a day,
 * represented by an integer according to the ISO-8601 standard, starting with
 * Monday as 1 through Sunday as 7.
 */
enum Weekday: int
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    /**
     * Returns the previous weekday.
     *
     * If the current instance is Monday, it wraps around and returns Sunday.
     *
     * @return Weekday The previous weekday.
     *
     * @psalm-mutation-free
     */
    public function getPrevious(): Weekday
    {
        return match ($this) {
            self::Monday => self::Sunday,
            self::Tuesday => self::Monday,
            self::Wednesday => self::Tuesday,
            self::Thursday => self::Wednesday,
            self::Friday => self::Thursday,
            self::Saturday => self::Friday,
            self::Sunday => self::Saturday,
        };
    }

    /**
     * Returns the next weekday.
     *
     * If the current instance is Sunday, it wraps around and returns Monday.
     *
     * @return Weekday The next weekday.
     *
     * @psalm-mutation-free
     */
    public function getNext(): Weekday
    {
        return match ($this) {
            self::Monday => self::Tuesday,
            self::Tuesday => self::Wednesday,
            self::Wednesday => self::Thursday,
            self::Thursday => self::Friday,
            self::Friday => self::Saturday,
            self::Saturday => self::Sunday,
            self::Sunday => self::Monday,
        };
    }
}
