<?php

declare(strict_types=1);

namespace Psl\DateTime;

/**
 * Represents the months of the year as an enum.
 *
 * This enum provides a type-safe way to work with months in a year, offering methods to
 * get the previous and next month, as well as determining the number of days in a month for a given year,
 * considering leap years. Each case in the enum corresponds to a month, represented by an integer
 * starting with January as 1 through December as 12.
 */
enum Month: int
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    /**
     * Returns the previous month.
     *
     * This method calculates and returns the month preceding the current instance of the Month enum.
     *
     * If the current instance is January, it wraps around and returns December.
     *
     * @return Month The previous month.
     *
     * @psalm-mutation-free
     */
    public function getPrevious(): Month
    {
        return match ($this) {
            self::January => self::December,
            self::February => self::January,
            self::March => self::February,
            self::April => self::March,
            self::May => self::April,
            self::June => self::May,
            self::July => self::June,
            self::August => self::July,
            self::September => self::August,
            self::October => self::September,
            self::November => self::October,
            self::December => self::November,
        };
    }

    /**
     * Returns the next month.
     *
     * This method calculates and returns the month succeeding the current instance of the Month enum.
     *
     * If the current instance is December, it wraps around and returns January.
     *
     * @return Month The next month.
     *
     * @psalm-mutation-free
     */
    public function getNext(): Month
    {
        return match ($this) {
            self::January => self::February,
            self::February => self::March,
            self::March => self::April,
            self::April => self::May,
            self::May => self::June,
            self::June => self::July,
            self::July => self::August,
            self::August => self::September,
            self::September => self::October,
            self::October => self::November,
            self::November => self::December,
            self::December => self::January,
        };
    }

    /**
     * Returns the number of days in the month for a given year.
     *
     * This method determines the number of days in the current month instance, considering whether the
     * provided year is a leap year or not. It uses separate methods for leap years and non-leap years
     * to get the appropriate day count.
     *
     * @param int $year The year for which the day count is needed.
     *
     * @return int<28, 31> The number of days in the month for the specified year.
     *
     * @psalm-mutation-free
     */
    public function getDaysForYear(int $year): int
    {
        if (namespace\is_leap_year($year)) {
            return $this->getLeapYearDays();
        }

        return $this->getNonLeapYearDays();
    }

    /**
     * Returns the number of days in the month for a non-leap year.
     *
     * This method provides the standard day count for the current month instance in a non-leap year.
     *
     * February returns 28, while April, June, September, and November return 30, and the rest return 31.
     *
     * @return int<28, 31> The number of days in the month for a non-leap year.
     *
     * @psalm-mutation-free
     */
    public function getNonLeapYearDays(): int
    {
        return match ($this) {
            self::January, self::March, self::May, self::July, self::August, self::October, self::December => 31,
            self::February => 28,
            self::April, self::June, self::September, self::November => 30,
        };
    }

    /**
     * Returns the number of days in the month for a leap year.
     *
     * This method provides the day count for the current month instance in a leap year.
     *
     * February returns 29, while April, June, September, and November return 30, and the rest return 31.
     *
     * @return int<29, 31> The number of days in the month for a leap year.
     *
     * @psalm-mutation-free
     */
    public function getLeapYearDays(): int
    {
        return match ($this) {
            self::January, self::March, self::May, self::July, self::August, self::October, self::December => 31,
            self::February => 29,
            self::April, self::June, self::September, self::November => 30,
        };
    }
}
