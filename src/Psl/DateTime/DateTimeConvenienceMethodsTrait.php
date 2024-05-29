<?php

declare(strict_types=1);

namespace Psl\DateTime;

use Psl\Locale\Locale;
use Psl\Math;

/**
 * @require-implements DateTimeInterface
 *
 * @psalm-immutable
 */
trait DateTimeConvenienceMethodsTrait
{
    use TemporalConvenienceMethodsTrait {
        toRfc3339 as private toRfc3339Impl;
    }

    /**
     * Checks if this {@see DateTimeInterface} instance is equal to the given {@see DateTimeInterface} instance including the timezone.
     *
     * @param DateTimeInterface $other The {@see DateTimeInterface} instance to compare with.
     *
     * @return bool True if equal including timezone, false otherwise.
     *
     * @psalm-mutation-free
     */
    public function equalsIncludingTimezone(DateTimeInterface $other): bool
    {
        return $this->equals($other) && $this->getTimezone() === $other->getTimezone();
    }

    /**
     * Obtains the timezone offset as a {@see Duration} object.
     *
     * This method effectively returns the offset from UTC for the timezone of this instance at the specific date and time it represents.
     *
     * It is equivalent to executing `$dt->getTimezone()->getOffset($dt)`, which calculates the offset for the timezone of this instance.
     *
     * @return Duration The offset from UTC as a Duration.
     *
     * @psalm-mutation-free
     */
    public function getTimezoneOffset(): Duration
    {
        return $this->getTimezone()->getOffset($this);
    }

    /**
     * Determines whether this instance is currently in daylight saving time.
     *
     * This method checks if the date and time represented by this instance fall within the daylight saving time period of its timezone.
     *
     * It is equivalent to `!$dt->getTimezone()->getDaylightSavingTimeOffset($dt)->isZero()`, indicating whether there is a non-zero DST offset.
     *
     * @return bool True if in daylight saving time, false otherwise.
     *
     * @psalm-mutation-free
     */
    public function isDaylightSavingTime(): bool
    {
        return !$this->getTimezone()->getDaylightSavingTimeOffset($this)->isZero();
    }

    /**
     * Converts the {@see DateTimeInterface} instance to the specified timezone.
     *
     * @param Timezone $timezone The timezone to convert to.
     *
     * @psalm-mutation-free
     */
    public function convertToTimezone(Timezone $timezone): static
    {
        return static::fromTimestamp($this->getTimestamp(), $timezone);
    }

    /**
     * Returns a new instance with the specified year.
     *
     * @throws Exception\UnexpectedValueException If the provided year do not align with calendar expectations.
     *
     * @psalm-mutation-free
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
     * @throws Exception\UnexpectedValueException If the provided month do not align with calendar expectations.
     *
     * @psalm-mutation-free
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
     * @throws Exception\UnexpectedValueException If the provided day do not align with calendar expectations.
     *
     * @psalm-mutation-free
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
     * @throws Exception\UnexpectedValueException If the provided hours do not align with calendar expectations.
     *
     * @psalm-mutation-free
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
     * @throws Exception\UnexpectedValueException If the provided minutes do not align with calendar expectations.
     *
     * @psalm-mutation-free
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
     * @throws Exception\UnexpectedValueException If the provided seconds do not align with calendar expectations.
     *
     * @psalm-mutation-free
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
     * @throws Exception\UnexpectedValueException If the provided nanoseconds do not align with calendar expectations.
     *
     * @psalm-mutation-free
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
     * @psalm-mutation-free
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
     * @psalm-mutation-free
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
     * Returns the {@see DateTimeInterface} parts (year, month, day, hours, minutes, seconds, nanoseconds).
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
     * @psalm-mutation-free
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
     * @psalm-mutation-free
     */
    public function getEra(): Era
    {
        return Era::fromYear($this->getYear());
    }

    /**
     * Returns the century number for the year stored in this object.
     *
     * @psalm-mutation-free
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
     * @psalm-mutation-free
     */
    public function getYearShort(): int
    {
        /** @var int<-99, 99> */
        return (int) $this->format(pattern: 'yy');
    }

    /**
     * Returns the month as an instance of the {@see Month} enum.
     *
     * This method converts the numeric representation of the month into its corresponding
     * case in the {@see Month} enum, providing a type-safe way to work with months.
     *
     * @return Month The month as an enum case.
     *
     * @psalm-mutation-free
     */
    public function getMonthEnum(): Month
    {
        return Month::from($this->getMonth());
    }

    /**
     * Returns the hours using the 12-hour format (1 to 12) along with the meridiem indicator.
     *
     * @return array{int<1, 12>, Meridiem} The hours and meridiem indicator.
     *
     * @psalm-mutation-free
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
     * @psalm-mutation-free
     */
    public function getISOWeekNumber(): array
    {
        /** @var int<1, 53> $week */
        $week = (int)$this->format(pattern: 'w');
        $year = (int)$this->format(pattern: 'Y');

        return [$year, $week];
    }

    /**
     * Gets the weekday of the date.
     *
     * @return Weekday The weekday.
     *
     * @psalm-mutation-free
     */
    public function getWeekday(): Weekday
    {
        return Weekday::from((int) $this->format(pattern: 'e'));
    }

    /**
     * Checks if the year is a leap year.
     *
     * @psalm-mutation-free
     */
    public function isLeapYear(): bool
    {
        return namespace\is_leap_year($this->getYear());
    }

    /**
     * Adds the specified years to this date-time object, returning a new instance with the added years.
     *
     * @throws Exception\UnderflowException If adding the years results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the years results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function plusYears(int $years): static
    {
        return $this->plusMonths($years * MONTHS_PER_YEAR);
    }

    /**
     * Subtracts the specified years from this date-time object, returning a new instance with the subtracted years.
     *
     * @throws Exception\UnderflowException If subtracting the years results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the years results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function minusYears(int $years): static
    {
        return $this->minusMonths($years * MONTHS_PER_YEAR);
    }

    /**
     * Adds the specified months to this date-time object, returning a new instance with the added months.
     *
     * @throws Exception\UnderflowException If adding the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the months results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function plusMonths(int $months): static
    {
        if (0 === $months) {
            return $this;
        }

        if (0 > $months) {
            return $this->minusMonths(-$months);
        }

        $current_year = $this->getYear();
        $current_month = $this->getMonthEnum();
        $days_to_add = 0;
        for ($i = 0; $i < $months; $i++) {
            $total_months = $current_month->value + $i;
            $target_year = $current_year + Math\div($total_months - 1, MONTHS_PER_YEAR);
            $target_month = $total_months % MONTHS_PER_YEAR;
            if ($target_month === 0) {
                $target_month = 1;
            }

            $days_to_add += Month::from($target_month)->getDaysForYear($target_year);
        }

        return $this->plus(Duration::days($days_to_add));
    }

    /**
     * Subtracts the specified months from this date-time object, returning a new instance with the subtracted months.
     *
     * @throws Exception\UnderflowException If subtracting the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the months results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function minusMonths(int $months): static
    {
        if (0 === $months) {
            return $this;
        }

        if (0 > $months) {
            return $this->plusMonths(-$months);
        }

        $current_year = $this->getYear();
        $current_month = $this->getMonthEnum();
        $days_to_subtract = 0;
        for ($i = 0; $i < $months; $i++) {
            // When subtracting, we need to move the current month back before the calculation
            $total_months = $current_month->value - $i;
            while ($total_months <= 0) {
                $total_months += MONTHS_PER_YEAR; // Adjust month to be within 1-12
                $current_year--; // Adjust year when wrapping
            }

            $target_month = ($total_months % MONTHS_PER_YEAR) ?: MONTHS_PER_YEAR;
            $target_year = $current_year + Math\div($total_months - 1, MONTHS_PER_YEAR);

            // Subtract days of the month we are moving into
            $days_to_subtract += Month::from($target_month)->getDaysForYear($target_year);
        }

        return $this->minus(Duration::days($days_to_subtract));
    }

    /**
     * Adds the specified days to this date-time object, returning a new instance with the added days.
     *
     * @throws Exception\UnderflowException If adding the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the days results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function plusDays(int $days): static
    {
        return $this->plus(Duration::days($days));
    }

    /**
     * Subtracts the specified days from this date-time object, returning a new instance with the subtracted days.
     *
     * @throws Exception\UnderflowException If subtracting the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the days results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function minusDays(int $days): static
    {
        return $this->minus(Duration::days($days));
    }

    /**
     * Adds the specified duration to this date-time object, returning a new instance with the added duration.
     *
     * @throws Exception\UnderflowException If adding the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the duration results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function plus(Duration $duration): static
    {
        return static::fromTimestamp($this->getTimestamp()->plus($duration), $this->timezone);
    }

    /**
     * Subtracts the specified duration from this date-time object, returning a new instance with the subtracted duration.
     *
     * @throws Exception\UnderflowException If subtracting the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the duration results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function minus(Duration $duration): static
    {
        return static::fromTimestamp($this->getTimestamp()->minus($duration), $this->timezone);
    }

    /**
     * Formats this {@see DateTimeInterface} instance based on a specific pattern, with optional customization for timezone and locale.
     *
     * This method allows for detailed customization of the output string by specifying a format pattern. If no pattern is provided,
     * a default, implementation-specific pattern will be used. Additionally, the method supports specifying a timezone and locale
     * for further customization of the formatted output. If these are not provided, system defaults will be used.
     *
     * Example usage:
     *
     * ```php
     * $formatted = $temporal->format('yyyy-MM-dd HH:mm:ss', $timezone, $locale);
     * ```
     *
     * @param null|FormatPattern|string $pattern Optional custom format pattern for the date and time. If null, uses a default pattern.
     * @param null|Timezone $timezone Optional timezone for formatting. If null, uses the current timezone.
     * @param null|Locale $locale Optional locale for formatting. If null, uses the system's default locale.
     *
     * @return string The formatted date and time string, according to the specified pattern, timezone, and locale.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     * @see Locale::default()
     *
     * @psalm-mutation-free
     */
    public function format(null|FormatPattern|string $pattern = null, null|Timezone $timezone = null, null|Locale $locale = null): string
    {
        $timestamp = $this->getTimestamp();

        /**
         * @psalm-suppress InvalidOperand
         * @psalm-suppress ImpureMethodCall
         */
        return Internal\create_intl_date_formatter(null, null, $pattern, $timezone ?? $this->getTimezone(), $locale)
            ->format($timestamp->getSeconds() + ($timestamp->getNanoseconds() / NANOSECONDS_PER_SECOND));
    }

    /**
     * Formats this {@see DateTimeInterface} instance to a string based on the RFC 3339 format, with additional
     * options for second fractions and timezone representation.
     *
     * The RFC 3339 format is widely adopted in web and network protocols for its unambiguous representation of date, time,
     * and timezone information. This method not only ensures universal readability but also the precise specification
     * of time across various systems, being compliant with both RFC 3339 and ISO 8601 standards.
     *
     * Example usage:
     *
     * ```php
     * // Default formatting
     * $rfc_formatted_string = $datetime->toRfc3339();
     * // Customized formatting with milliseconds and 'Z' for UTC
     * $rfc_formatted_string_with_milliseconds_and_z = $datetime->toRfc3339(SecondsStyle::Milliseconds, true);
     * ```
     *
     * @param null|SecondsStyle $seconds_style Optional parameter to specify the seconds formatting style. Automatically
     *                                         selected based on precision if null.
     * @param bool $use_z Determines the representation of UTC timezone. True to use 'Z', false to use the standard offset format.
     *
     * @return string The formatted string of the {@see DateTimeInterface} instance, adhering to the RFC 3339 and compatible with ISO 8601 formats.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3339
     *
     * @psalm-mutation-free
     */
    public function toRfc3339(?SecondsStyle $seconds_style = null, bool $use_z = false): string
    {
        return Internal\format_rfc3339($this->getTimestamp(), $seconds_style, $use_z, $this->getTimezone());
    }

    /**
     * Provides a string representation of this {@see TemporalInterface} instance, formatted according to specified styles for date and time,
     * and optionally adjusted for a specific timezone and locale.
     *
     * This method offers a higher-level abstraction for formatting, allowing users to specify styles for date and time separately
     * rather than a custom pattern. If no styles are provided, default styles will be used.
     *
     * Additionally, the timezone and locale can be specified for locale-sensitive formatting.
     *
     * Example usage:
     *
     * ```php
     * $string_representation = $temporal->toString(FormatDateStyle::Long, FormatTimeStyle::Short, $timezone, $locale);
     * ```
     *
     * @param null|DateStyle $date_style Optional style for the date portion of the output. If null, a default style is used.
     * @param null|TimeStyle $time_style Optional style for the time portion of the output. If null, a default style is used.
     * @param null|Timezone $timezone Optional timezone for formatting. If null, uses the current timezone.
     * @param null|Locale $locale Optional locale for formatting. If null, uses the system's default locale.
     *
     * @return string The string representation of the date and time, formatted according to the specified styles, timezone, and locale.
     *
     * @see DateStyle::default()
     * @see TimeStyle::default()
     * @see Locale::default()
     *
     * @psalm-mutation-free
     */
    public function toString(null|DateStyle $date_style = null, null|TimeStyle $time_style = null, null|Timezone $timezone = null, null|Locale $locale = null): string
    {
        $timestamp = $this->getTimestamp();

        /**
         * @psalm-suppress InvalidOperand
         * @psalm-suppress ImpureMethodCall
         */
        return Internal\create_intl_date_formatter($date_style, $time_style, null, $timezone ?? $this->getTimezone(), $locale)
            ->format($timestamp->getSeconds() + ($timestamp->getNanoseconds() / NANOSECONDS_PER_SECOND));
    }
}
