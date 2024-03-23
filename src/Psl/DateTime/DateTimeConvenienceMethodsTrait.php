<?php

declare(strict_types=1);

namespace Psl\DateTime;

use Psl\DateTime;
use Psl\Locale;
use Psl\Math;

/**
 * @require-implements DateTimeInterface
 */
trait DateTimeConvenienceMethodsTrait
{
    use TemporalConvenienceMethodsTrait;

    /**
     * Checks if this {@see DateTimeInterface} instance is equal to the given DateTime instance including the timezone.
     *
     * @param DateTimeInterface $other The {@see DateTimeInterface} instance to compare with.
     *
     * @return bool True if equal including timezone, false otherwise.
     *
     * @mutation-free
     */
    public function equalsIncludingTimezone(DateTimeInterface $other): bool
    {
        return $this->equals($other) && $this->getTimezone() === $other->getTimezone();
    }

    /**
     * Returns a new instance with the specified year.
     *
     * @throws Exception\InvalidArgumentException If changing the year would result in an invalid date (e.g., setting a year that turns February 29th into a date in a non-leap year).
     *
     * @mutation-free
     */
    public function withYear(int $year): static
    {
        return $this->withDate($year, $this->getMonth(), $this->getDay());
    }

    /**
     * Returns a new instance with the specified month.
     *
     * @param Month|int<1, 12> $month
     *
     * @throws Exception\InvalidArgumentException If changing the month would result in an invalid date/time.
     *
     * @mutation-free
     */
    public function withMonth(Month|int $month): static
    {
        return $this->withDate($this->getYear(), $month, $this->getDay());
    }

    /**
     * Returns a new instance with the specified day.
     *
     * @param int<1, 31> $day
     *
     * @throws Exception\InvalidArgumentException If changing the day would result in an invalid date/time.
     *
     * @mutation-free
     */
    public function withDay(int $day): static
    {
        return $this->withDate($this->getYear(), $this->getMonth(), $day);
    }

    /**
     * Returns a new instance with the specified hours.
     *
     * @param int<0, 23> $hours
     *
     * @throws Exception\InvalidArgumentException If specifying the hours would result in an invalid time (e.g., hours greater than 23).
     *
     * @mutation-free
     */
    public function withHours(int $hours): static
    {
        return $this->withTime($hours, $this->getMinutes(), $this->getSeconds(), $this->getNanoseconds());
    }

    /**
     * Returns a new instance with the specified minutes.
     *
     * @param int<0, 59> $minutes
     *
     * @throws Exception\InvalidArgumentException If specifying the minutes would result in an invalid time (e.g., minutes greater than 59).
     *
     * @mutation-free
     */
    public function withMinutes(int $minutes): static
    {
        return $this->withTime($this->getHours(), $minutes, $this->getSeconds(), $this->getNanoseconds());
    }

    /**
     * Returns a new instance with the specified seconds.
     *
     * @param int<0, 59> $seconds
     *
     * @throws Exception\InvalidArgumentException If specifying the seconds would result in an invalid time (e.g., seconds greater than 59).
     *
     * @mutation-free
     */
    public function withSeconds(int $seconds): static
    {
        return $this->withTime($this->getHours(), $this->getMinutes(), $seconds, $this->getNanoseconds());
    }

    /**
     * Returns a new instance with the specified nanoseconds.
     *
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\InvalidArgumentException If specifying the nanoseconds would result in an invalid time, considering that valid nanoseconds range from 0 to 999,999,999.
     *
     * @mutation-free
     */
    public function withNanoseconds(int $nanoseconds): static
    {
        return $this->withTime($this->getHours(), $this->getMinutes(), $this->getSeconds(), $nanoseconds);
    }

    /**
     * Returns the date (year, month, day).
     *
     * @return array{int, int<1, 12>, int<1, 31>} The date.
     *
     * @mutation-free
     */
    public function getDate(): array
    {
        return [$this->getYear(), $this->getMonth(), $this->getDay()];
    }

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
    public function getTime(): array
    {
        return [
            $this->getHours(),
            $this->getMinutes(),
            $this->getSeconds(),
            $this->getNanoseconds(),
        ];
    }

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
    public function getParts(): array
    {
        return [
            $this->getYear(),
            $this->getMonth(),
            $this->getDay(),
            $this->getHours(),
            $this->getMinutes(),
            $this->getSeconds(),
            $this->getNanoseconds(),
        ];
    }

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
    public function getEra(): Era
    {
        return Era::fromYear($this->getYear());
    }

    /**
     * Returns the century number for the year stored in this object.
     *
     * @mutation-free
     */
    public function getCentury(): int
    {
        return (int)($this->getYear() / 100) + 1;
    }

    /**
     * Returns the short format of the year (last 2 digits).
     *
     * @return int<-99, 99> The short format of the year.
     *
     * @mutation-free
     */
    public function getYearShort(): int
    {
        return $this->getYear() % 100;
    }

    /**
     * Returns the hours using the 12-hour format (1 to 12) along with the meridiem indicator.
     *
     * @return array{int<1, 12>, Meridiem} The hours and meridiem indicator.
     *
     * @mutation-free
     */
    public function getTwelveHours(): array
    {
        return [
            ($this->getHours() % 12 ?: 12),
            ($this->getHours() < 12 ? Meridiem::AnteMeridiem : Meridiem::PostMeridiem),
        ];
    }

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
    public function getISOWeekNumber(): array
    {
        $year = $this->getYear();
        $week = (int)$this->format('W'); // Correct format specifier for ISO-8601 week number is 'W', not '%V'

        // Adjust the year based on ISO week numbering rules
        if ($week === 1 && $this->getMonth() === 12) {
            ++$year; // Belongs to the first week of the next year
        } elseif ($week > 50 && $this->getMonth() === 1) {
            --$year; // Belongs to the last week of the previous year
        }

        return [$year, $week];
    }

    /**
     * Gets the weekday of the date.
     *
     * @return Weekday The weekday.
     *
     * @mutation-free
     */
    public function getWeekday(): Weekday
    {
        return Weekday::from((int)$this->format('%u'));
    }

    /**
     * Checks if the date and time is in daylight saving time.
     *
     * @return bool True if in daylight saving time, false otherwise.
     *
     * @mutation-free
     */
    public function isDaylightSavingTime(): bool
    {
        return !$this->getTimezone()->getDaylightSavingTimeOffset($this)->isZero();
    }

    /**
     * Returns whether the year stored in this DateTime object is a leap year
     * (has 366 days including February 29).
     */
    public function isLeapYear(): bool
    {
        return DateTime\is_leap_year($this->year);
    }

    /**
     * Adds the specified years to this date-time object, returning a new instance with the added years.
     *
     * @throws Exception\UnderflowException If adding the years results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the years results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusYears(int $years): static
    {
        if ($years < 0) {
            return $this->minusYears(-$years);
        }

        $current_year = $this->getYear();

        // Check for potential overflow when adding years
        if ($current_year >= Math\INT64_MAX - $years || $current_year <= Math\INT64_MIN - $years) {
            throw new Exception\OverflowException("Adding years results in a year that exceeds the representable integer range.");
        }

        $target_year = $current_year + $years;

        // Handle the day and month for the target year, considering leap years
        $current_month = $this->getMonth();
        $current_day = $this->getDay();

        $days_in_target_month = Month::from($current_month)->getDaysForYear($target_year);
        // February 29 adjustment for non-leap target years
        $target_day = Math\minva($days_in_target_month, $current_day);

        return $this->withDate($target_year, $current_month, $target_day);
    }

    /**
     * Subtracts the specified years from this date-time object, returning a new instance with the subtracted years.
     *
     * @throws Exception\UnderflowException If subtracting the years results in an arithmetic underflow.
     *
     * @mutation-free
     */
    public function minusYears(int $years): static
    {
        if ($years < 0) {
            return $this->plusYears(-$years);
        }

        $current_year = $this->getYear();

        // Check for potential underflow when subtracting years
        if ($current_year <= Math\INT64_MIN + $years) {
            throw new Exception\UnderflowException("Subtracting years results in a year that underflows the representable integer range.");
        }

        $target_year = $current_year - $years;

        $current_month = $this->getMonth();
        $current_day = $this->getDay();

        $days_in_target_month = Month::from($current_month)->getDaysForYear($target_year);
        $target_day = Math\minva($current_day, $days_in_target_month);

        return $this->withDate($target_year, $current_month, $target_day);
    }

    /**
     * Adds the specified months to this date-time object, returning a new instance with the added months.
     *
     * @throws Exception\UnderflowException If adding the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the months results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusMonths(int $months): static
    {
        if ($months < 0) {
            return $this->minusMonths(-$months);
        }

        $current_day = $this->getDay();
        $current_month = $this->getMonth();
        $current_year = $this->getYear();

        if ($current_year >= Math\INT64_MAX) {
            throw new Exception\OverflowException("Cannot add months to the maximum year.");
        }

        // Calculate target month and year
        $total_months = $current_month + $months;
        $target_year = $current_year + Math\div($total_months - 1, 12);
        if ($target_year >= Math\INT64_MAX || $target_year <= Math\INT64_MIN) {
            throw new Exception\OverflowException("Adding months results in a year that exceeds the representable integer range.");
        }

        $target_month = $total_months % 12;
        if ($target_month === 0) {
            $target_month = 12;
            --$target_year; // Adjust if the modulo brings us back to December of the previous year
        }

        // Days adjustment for end-of-month scenarios
        $days_in_target_month = Month::from($target_month)->getDaysForYear($target_year);

        $target_day = Math\minva($current_day, $days_in_target_month);

        // Assuming withDate properly constructs a new instance
        return $this->withDate($target_year, $target_month, $target_day);
    }

    /**
     * Subtracts the specified months from this date-time object, returning a new instance with the subtracted months.
     *
     * @throws Exception\UnderflowException If subtracting the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the months results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusMonths(int $months): static
    {
        if ($months < 0) {
            return $this->plusMonths(-$months);
        }

        $current_day = $this->getDay();
        $current_month = $this->getMonth();
        $current_year = $this->getYear();

        // Calculate how many years to subtract based on the months
        $years_to_subtract = Math\div($months, 12);
        $months_to_subtract_after_years = $months % 12;

        // Check for potential underflow when subtracting months
        if ($current_year <= Math\INT64_MIN + $years_to_subtract) {
            throw new Exception\UnderflowException("Subtracting months results in a year that underflows the representable integer range.");
        }

        $new_year = $current_year - $years_to_subtract;
        $new_month = $current_month - $months_to_subtract_after_years;

        // Adjust if new_month goes below 1 (January)
        if ($new_month < 1) {
            $new_month += 12; // Cycle back to December
            --$new_year;   // Adjust the year since we cycled back
        }

        // Ensure year adjustment didn't underflow
        if ($new_year < Math\INT64_MIN) {
            throw new Exception\UnderflowException("Subtracting months results in a year that underflows the representable integer range.");
        }

        $days_in_new_month = Month::from($new_month)->getDaysForYear($new_year);
        $new_day = Math\minva($current_day, $days_in_new_month);

        return $this->withDate($new_year, $new_month, $new_day);
    }

    /**
     * Adds the specified days to this date-time object, returning a new instance with the added days.
     *
     * @throws Exception\UnderflowException If adding the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the days results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusDays(int $days): static
    {
        return $this->plusHours(HOURS_PER_DAY * $days);
    }

    /**
     * Subtracts the specified days from this date-time object, returning a new instance with the subtracted days.
     *
     * @throws Exception\UnderflowException If subtracting the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the days results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusDays(int $days): static
    {
        return $this->plusDays(-$days);
    }

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
    public function format(DatePattern|string|null $pattern = null, ?Timezone $timezone = null, ?Locale\Locale $locale = null): string
    {
        return $this->getTimestamp()->format($pattern, $timezone ?? $this->getTimezone(), $locale);
    }
}
