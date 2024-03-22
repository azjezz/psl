<?php

declare(strict_types=1);

namespace Psl\DateTime;

use Psl\Locale;

interface DateTimeInterface extends TemporalInterface
{
    /**
     * Checks if this {@see DateTimeInterface} instance is equal to the given {@see DateTimeInterface} instance including the timezone.
     *
     * @param DateTimeInterface $other The {@see DateTimeInterface} instance to compare with.
     *
     * @return bool True if equal including timezone, false otherwise.
     *
     * @mutation-free
     */
    public function equalsIncludingTimezone(DateTimeInterface $other): bool;

    /**
     * Returns a new instance with the specified date.
     *
     * @param Month|int<1, 12> $month
     * @param int<1, 31> $day
     *
     * @throws Exception\InvalidArgumentException If specifying the date would result in an invalid date/time.
     *                                            This can happen if the combination of year, month, and day does not constitute a valid date (e.g., April 31st, February 29th in a non-leap year).
     *
     * @mutation-free
     */
    public function withDate(int $year, Month|int $month, int $day): static;

    /**
     * Returns a new instance with the specified time.
     *
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     * @param int<0, 59> $seconds
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\InvalidArgumentException If specifying the time would result in an invalid time (e.g., hours greater than 23, minutes or seconds greater than 59).
     *
     * @mutation-free
     */
    public function withTime(int $hours, int $minutes, int $seconds = 0, int $nanoseconds = 0): static;

    /**
     * Returns a new instance with the specified year.
     *
     * @throws Exception\InvalidArgumentException If changing the year would result in an invalid date (e.g., setting a year that turns February 29th into a date in a non-leap year).
     *
     * @mutation-free
     */
    public function withYear(int $year): static;

    /**
     * Returns a new instance with the specified month.
     *
     * @param Month|int<1, 12> $month
     *
     * @throws Exception\InvalidArgumentException If changing the month would result in an invalid date/time.
     *
     * @mutation-free
     */
    public function withMonth(Month|int $month): static;

    /**
     * Returns a new instance with the specified day.
     *
     * @param int<1, 31> $day
     *
     * @throws Exception\InvalidArgumentException If changing the day would result in an invalid date/time.
     *
     * @mutation-free
     */
    public function withDay(int $day): static;

    /**
     * Returns a new instance with the specified hours.
     *
     * @param int<0, 23> $hours
     *
     * @throws Exception\InvalidArgumentException If specifying the hours would result in an invalid time (e.g., hours greater than 23).
     *
     * @mutation-free
     */
    public function withHours(int $hours): static;

    /**
     * Returns a new instance with the specified minutes.
     *
     * @param int<0, 59> $minutes
     *
     * @throws Exception\InvalidArgumentException If specifying the minutes would result in an invalid time (e.g., minutes greater than 59).
     *
     * @mutation-free
     */
    public function withMinutes(int $minutes): static;

    /**
     * Returns a new instance with the specified seconds.
     *
     * @param int<0, 59> $seconds
     *
     * @throws Exception\InvalidArgumentException If specifying the seconds would result in an invalid time (e.g., seconds greater than 59).
     *
     * @mutation-free
     */
    public function withSeconds(int $seconds): static;

    /**
     * Returns a new instance with the specified nanoseconds.
     *
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\InvalidArgumentException If specifying the nanoseconds would result in an invalid time, considering that valid nanoseconds range from 0 to 999,999,999.
     *
     * @mutation-free
     */
    public function withNanoseconds(int $nanoseconds): static;

    /**
     * Returns the date (year, month, day).
     *
     * @return array{int, int<1, 12>, int<1, 31>} The date.
     *
     * @mutation-free
     */
    public function getDate(): array;

    /**
     * Returns the time (hours, minutes, seconds, nanoseconds).
     *
     * @return array{
     *     int<0, 23>,
     *     int<0, 59>,
     *     int<0, 59>,
     *     int<0, 999999999>,
     * }
     *
     * @mutation-free
     */
    public function getTime(): array;

    /**
     * Returns the date and time parts (year, month, day, hours, minutes, seconds, nanoseconds).
     *
     * @return array{
     *     int,
     *     int<1, 12>,
     *     int<1, 31>,
     *     int<0, 23>,
     *     int<0, 59>,
     *     int<0, 59>,
     *     int<0, 999999999>,
     * }
     *
     * @mutation-free
     */
    public function getParts(): array;

    /**
     * Retrieves the era of the date represented by this DateTime instance.
     *
     * This method returns an instance of the `Era` enum, which indicates whether the date
     * falls in the Anno Domini (AD) or Before Christ (BC) era. The era is determined based on the year
     * of the date this object represents, with years designated as BC being negative
     * and years in AD being positive.
     *
     * @mutation-free
     */
    public function getEra(): Era;

    /**
     * Returns the century number for the year stored in this object.
     *
     * @mutation-free
     */
    public function getCentury(): int;

    /**
     * Retrieves the year as an integer, following ISO-8601 conventions for numbering.
     *
     * This method returns the year part of the date. For years in the Anno Domini (AD) era, the returned value matches
     * the Gregorian calendar year directly (e.g., 1 for AD 1, 2021 for AD 2021, etc.). For years before AD 1, the method
     * adheres to the ISO-8601 standard, which does not use a year zero: 1 BC is represented as 0, 2 BC as -1, 3 BC as -2,
     * and so forth. This ISO-8601 numbering facilitates straightforward mathematical operations on years across the AD/BC
     * divide but may require conversion for user-friendly display or when interfacing with systems that use the traditional
     * AD/BC notation.
     *
     * @return int The year, formatted according to ISO-8601 standards, where 1 AD is 1, 1 BC is 0, 2 BC is -1, etc.
     *
     * @mutation-free
     */
    public function getYear(): int;

    /**
     * Returns the short format of the year (last 2 digits).
     *
     * @return int<00, 99> The short format of the year.
     *
     * @mutation-free
     */
    public function getYearShort(): int;

    /**
     * Returns the month.
     *
     * @return int<1, 12>
     *
     * @mutation-free
     */
    public function getMonth(): int;

    /**
     * Returns the day.
     *
     * @return int<0, 31>
     *
     * @mutation-free
     */
    public function getDay(): int;

    /**
     * Returns the hours.
     *
     * @return int<0, 23>
     *
     * @mutation-free
     */
    public function getHours(): int;

    /**
     * Returns the hours using the 12-hour format (1 to 12) along with the meridiem indicator.
     *
     * @return array{int<1, 12>, Meridiem} The hours and meridiem indicator.
     *
     * @mutation-free
     */
    public function getTwelveHours(): array;

    /**
     * Returns the minutes.
     *
     * @return int<0, 59>
     *
     * @mutation-free
     */
    public function getMinutes(): int;

    /**
     * Returns the seconds.
     *
     * @return int<0, 59>
     *
     * @mutation-free
     */
    public function getSeconds(): int;

    /**
     * Returns the nanoseconds.
     *
     * @return int<0, 999999999>
     *
     * @mutation-free
     */
    public function getNanoseconds(): int;

    /**
     * Retrieves the ISO-8601 year and week number corresponding to the date.
     *
     * This method returns an array consisting of two integers: the first represents the year, and the second
     * represents the week number according to ISO-8601 standards, which ranges from 1 to 53. The week numbering
     * follows the ISO-8601 specification, where a week starts on a Monday and the first week of the year is the
     * one that contains at least four days of the new year.
     *
     * Due to the ISO-8601 week numbering rules, the returned year might not always match the Gregorian year
     * obtained from `$this->getYear()`. Specifically:
     *
     *  - The first few days of January might belong to the last week of the previous year if they fall before
     *      the first Thursday of January.
     *
     *  - Conversely, the last days of December might be part of the first week of the following year if they
     *      extend beyond the last Thursday of December.
     *
     * Examples:
     *  - For the date 2020-01-01, it returns [2020, 1], indicating the first week of 2020.
     *  - For the date 2021-01-01, it returns [2020, 53], showing that this day is part of the last week of 2020
     *      according to ISO-8601.
     *
     * @return array{int, int<1, 53>}
     *
     * @mutation-free
     */
    public function getISOWeekNumber(): array;

    /**
     * Gets the timezone associated with the date and time.
     *
     * @mutation-free
     */
    public function getTimezone(): Timezone;

    /**
     * Gets the weekday of the date.
     *
     * @return Weekday The weekday.
     *
     * @mutation-free
     */
    public function getWeekday(): Weekday;

    /**
     * Checks if the date and time is in daylight saving time.
     *
     * @return bool True if in daylight saving time, false otherwise.
     *
     * @mutation-free
     */
    public function isDaylightSavingTime(): bool;

    /**
     * Checks if the year is a leap year.
     *
     * @mutation-free
     */
    public function isLeapYear(): bool;

    /**
     * Adds the specified years to this date-time object, returning a new instance with the added years.
     *
     * @throws Exception\UnderflowException If adding the years results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the years results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusYears(int $years): static;

    /**
     * Adds the specified months to this date-time object, returning a new instance with the added months.
     *
     * @throws Exception\UnderflowException If adding the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the months results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusMonths(int $months): static;

    /**
     * Adds the specified days to this date-time object, returning a new instance with the added days.
     *
     * @throws Exception\UnderflowException If adding the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the days results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusDays(int $days): static;

    /**
     * Subtracts the specified years from this date-time object, returning a new instance with the subtracted years.
     *
     * @throws Exception\UnderflowException If subtracting the years results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the years results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusYears(int $years): static;

    /**
     * Subtracts the specified months from this date-time object, returning a new instance with the subtracted months.
     *
     * @throws Exception\UnderflowException If subtracting the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the months results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusMonths(int $months): static;

    /**
     * Subtracts the specified days from this date-time object, returning a new instance with the subtracted days.
     *
     * @throws Exception\UnderflowException If subtracting the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the days results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusDays(int $days): static;

    /**
     * Converts the date and time to the specified timezone.
     *
     * @param Timezone $timezone The timezone to convert to.
     *
     * @mutation-free
     */
    public function convertToTimezone(Timezone $timezone): static;

    /**
     * Formats the date and time of this instance into a string based on the provided pattern, timezone, and locale.
     *
     * If no pattern is specified, a default pattern will be used.
     *
     * The method uses the associated timezone of the instance by default, but this can be overridden with the
     * provided timezone parameter.
     *
     * The method also accounts for locale-specific formatting rules if a locale is provided.
     *
     * @mutation-free
     *
     * @note The default pattern is subject to change at any time and should not be relied upon for consistent formatting.
     */
    public function format(DatePattern|string|null $pattern = null, ?Timezone $timezone = null, ?Locale\Locale $locale = null): string;
}
