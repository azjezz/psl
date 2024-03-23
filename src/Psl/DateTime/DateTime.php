<?php

declare(strict_types=1);

namespace Psl\DateTime;

use IntlCalendar;
use Psl\Locale\Locale;

/**
 * @immutable
 */
final class DateTime implements DateTimeInterface
{
    use DateTimeConvenienceMethodsTrait;

    private readonly Timezone $timezone;

    private readonly Timestamp $timestamp;

    private readonly int $year;

    /**
     * @var int<1, 12>
     */
    private readonly int $month;

    /**
     * @var int<1, 31>
     */
    private readonly int $day;

    /**
     * @var int<0, 23>
     */
    private readonly int $hours;

    /**
     * @var int<0, 59>
     */
    private readonly int $minutes;

    /**
     * @var int<0, 59>
     */
    private readonly int $seconds;

    /**
     * @var int<0, 999999999>
     */
    private readonly int $nanoseconds;

    /**
     * Constructs a new date-time instance with specified components and timezone.
     *
     * This constructor initializes a date-time object with the provided year, month, day, hour, minute,
     * second, and nanosecond components within the given timezone. It ensures that all components are within their
     * valid ranges: nanoseconds [0, 999,999,999], seconds [0, 59], minutes [0, 59], hours [0, 23], month [1, 12],
     * and day [1, 28-31] depending on the month and leap year status. The constructor validates these components and
     * assigns them to the instance if they are valid. If any component is out of its valid range, an
     * `Exception\InvalidDateTimeException` is thrown.
     *
     * @throws Exception\InvalidArgumentException If any of the date or time components are outside their valid ranges,
     *                                            indicating an invalid date-time configuration.
     *
     * @psalm-external-mutation-free
     */
    private function __construct(Timezone $timezone, Timestamp $timestamp, int $year, int $month, int $day, int $hours, int $minutes, int $seconds, int $nanoseconds)
    {
        if (
            $nanoseconds < 0 || $nanoseconds >= NANOSECONDS_PER_SECOND ||
            $seconds < 0 || $seconds >= 60 ||
            $minutes < 0 || $minutes >= 60 ||
            $hours < 0 || $hours >= 24 ||
            $month < 1 || $month > 12 ||
            $day < 1 || $day > 31 || $day > Month::from($month)->getDaysForYear($year)
        ) {
            throw new Exception\InvalidArgumentException('One or more components of the date-time are out of valid ranges.');
        }

        $this->timestamp = $timestamp;
        $this->timezone = $timezone;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
        $this->nanoseconds = $nanoseconds;
    }

    /**
     * Creates a new {@see DateTime} instance representing the current moment.
     *
     * This static method returns a {@see DateTime} object set to the current date and time. If a specific timezone is
     * provided, the returned {@see DateTime} will be adjusted to reflect the date and time in that timezone.
     */
    public static function now(?Timezone $timezone = null): DateTime
    {
        return self::fromTimestamp($timezone ?? Timezone::default(), Timestamp::now());
    }

    /**
     * Creates a DateTime instance for a specific time on the current day within the specified timezone.
     *
     * This method facilitates the creation of a {@see DateTime} object representing a precise time on today's date. It is
     * particularly useful when you need to set a specific time of day for the current date in a given timezone. The
     * method combines the current date context with a specific time, offering a convenient way to specify times such
     * as "today at 14:00" in code.
     *
     * The time components (hours, minutes, seconds, nanoseconds) must be within their valid ranges. The method
     * enforces these constraints and throws an {@see Exception\InvalidArgumentException} if any component is out of bounds.
     *
     * @param int<0, 23> $hours The hour component of the time, ranging from 0 to 23.
     * @param int<0, 59> $minutes The minute component of the time, ranging from 0 to 59.
     * @param int<0, 59> $seconds The second component of the time, defaulting to 0, and ranging from 0 to 59.
     * @param int<0, 999999999> $nanoseconds The nanosecond component of the time, defaulting to 0, and ranging from 0 to 999,999,999.
     *
     * @throws Exception\InvalidArgumentException If any of the time components are outside their valid ranges,
     *                                            indicating an invalid date-time configuration.
     */
    public static function todayAt(int $hours, int $minutes, int $seconds = 0, int $nanoseconds = 0, ?Timezone $timezone = null): DateTime
    {
        return self::now($timezone)->withTime($hours, $minutes, $seconds, $nanoseconds);
    }

    /**
     * Creates a {@see DateTime} instance from individual date and time components.
     *
     * Note: In cases where the specified time occurs twice (such as during the end of daylight saving time), the earlier occurrence
     * is returned. To obtain the later occurrence, you can adjust the returned instance using `->plusHours(1)`.
     *
     * @param Month|int<1, 12> $month
     * @param int<1, 31> $day
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     * @param int<0, 59> $seconds
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\InvalidArgumentException If the combination of date-time components is invalid.
     *
     * @pure
     */
    public static function fromParts(Timezone $timezone, int $year, Month|int $month, int $day, int $hours = 0, int $minutes = 0, int $seconds = 0, int $nanoseconds = 0): self
    {
        if ($month instanceof Month) {
            $month = $month->value;
        }

        /**
         * @var IntlCalendar $calendar
         */
        $calendar = IntlCalendar::createInstance(
            Internal\to_intl_timezone($timezone),
        );

        /** @psalm-suppress ImpureMethodCall */
        $calendar->set($year, $month - 1, $day, $hours, $minutes, $seconds);

        // Validate the date-time components by comparing them to what was set
        if (
            !(/** @psalm-suppress ImpureMethodCall */ $calendar->get(IntlCalendar::FIELD_YEAR) === $year &&
                (/** @psalm-suppress ImpureMethodCall */ $calendar->get(IntlCalendar::FIELD_MONTH) + 1) === $month &&
/** @psalm-suppress ImpureMethodCall */ $calendar->get(IntlCalendar::FIELD_DAY_OF_MONTH) === $day &&
/** @psalm-suppress ImpureMethodCall */ $calendar->get(IntlCalendar::FIELD_HOUR_OF_DAY) === $hours &&
/** @psalm-suppress ImpureMethodCall */ $calendar->get(IntlCalendar::FIELD_MINUTE) === $minutes &&
/** @psalm-suppress ImpureMethodCall */ $calendar->get(IntlCalendar::FIELD_SECOND) === $seconds)
        ) {
            throw new Exception\InvalidArgumentException(
                'The given components do not form a valid date-time.',
            );
        }

        /** @psalm-suppress ImpureMethodCall */
        $timestampInSeconds = (int) ($calendar->getTime() / MILLISECONDS_PER_SECOND);
        $timestamp = Timestamp::fromRaw($timestampInSeconds, $nanoseconds);

        return new self(
            $timezone,
            $timestamp,
            $year,
            $month,
            $day,
            $hours,
            $minutes,
            $seconds,
            $nanoseconds
        );
    }

    /**
     * Creates a {@see DateTime} instance from a timestamp, representing the same point in time.
     *
     * This method converts a {@see Timestamp} into a {@see DateTime} instance calculated for the specified timezone.
     *
     * @pure
     */
    public static function fromTimestamp(Timezone $timezone, Timestamp $timestamp): DateTime
    {
        /**
         * @var IntlCalendar $calendar
         *
         * @psalm-suppress ImpureMethodCall
         */
        $calendar = IntlCalendar::createInstance(
            Internal\to_intl_timezone($timezone),
        );

        /** @psalm-suppress ImpureMethodCall */
        $calendar->setTime(
            $timestamp->getSeconds() * MILLISECONDS_PER_SECOND,
        );

        /** @psalm-suppress ImpureMethodCall */
        $year = $calendar->get(IntlCalendar::FIELD_YEAR);
        /** @psalm-suppress ImpureMethodCall */
        $month = $calendar->get(IntlCalendar::FIELD_MONTH) + 1;
        /** @psalm-suppress ImpureMethodCall */
        $day = $calendar->get(IntlCalendar::FIELD_DAY_OF_MONTH);
        /** @psalm-suppress ImpureMethodCall */
        $hour = $calendar->get(IntlCalendar::FIELD_HOUR_OF_DAY);
        /** @psalm-suppress ImpureMethodCall */
        $minute = $calendar->get(IntlCalendar::FIELD_MINUTE);
        /** @psalm-suppress ImpureMethodCall */
        $second = $calendar->get(IntlCalendar::FIELD_SECOND);
        /** @psalm-suppress ImpureMethodCall */
        $nanoseconds = $timestamp->getNanoseconds();

        /** @psalm-suppress MissingThrowsDocblock */
        return new static(
            $timezone,
            $timestamp,
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second,
            $nanoseconds,
        );
    }

    /**
     * Creates a {@see DateTime} instance from a date/time string according to a specific pattern.
     *
     * This method allows parsing of date/time strings that conform to custom patterns,
     * making it versatile for handling various date/time formats.
     *
     * @throws Exception\RuntimeException If parsing fails or the date/time string is invalid.
     *
     * @pure
     */
    public static function fromPattern(string|DatePattern $pattern, string $raw_string, Timezone $timezone, ?Locale $locale = null): self
    {
        return self::fromTimestamp($timezone, Timestamp::fromPattern($pattern, $raw_string, $timezone, $locale));
    }

    /**
     * Parses a date/time string into a {@see DateTime} instance.
     *
     * This method is a convenience wrapper for parsing date/time strings without specifying a custom pattern.
     *
     * @throws Exception\RuntimeException If parsing fails or the format of the date/time string is invalid.
     *
     * @pure
     */
    public static function parse(string $raw_string, Timezone $timezone, ?Locale $locale = null): self
    {
        return self::fromTimestamp($timezone, Timestamp::parse($raw_string, $timezone, $locale));
    }

    /**
     * Returns the timestamp representation of this date time object.
     *
     * @mutation-free
     */
    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

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
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Returns the month.
     *
     * @return int<1, 12>
     *
     * @mutation-free
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * Returns the day.
     *
     * @return int<1, 31>
     *
     * @mutation-free
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * Returns the hours.
     *
     * @return int<0, 23>
     *
     * @mutation-free
     */
    public function getHours(): int
    {
        return $this->hours;
    }

    /**
     * Returns the minutes.
     *
     * @return int<0, 59>
     *
     * @mutation-free
     */
    public function getMinutes(): int
    {
        return $this->minutes;
    }

    /**
     * Returns the seconds.
     *
     * @return int<0, 59>
     *
     * @mutation-free
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Returns the nanoseconds.
     *
     * @return int<0, 999999999>
     *
     * @mutation-free
     */
    public function getNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * Gets the timezone associated with the date and time.
     *
     * @mutation-free
     */
    public function getTimezone(): Timezone
    {
        return $this->timezone;
    }

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
    public function withDate(int $year, Month|int $month, int $day): static
    {
        return static::fromParts(
            $this->getTimezone(),
            $year,
            $month,
            $day,
            $this->getHours(),
            $this->getMinutes(),
            $this->getSeconds(),
            $this->getNanoseconds(),
        );
    }

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
    public function withTime(int $hours, int $minutes, int $seconds = 0, int $nanoseconds = 0): static
    {
        return static::fromParts(
            $this->getTimezone(),
            $this->getYear(),
            $this->getMonth(),
            $this->getDay(),
            $hours,
            $minutes,
            $seconds,
            $nanoseconds,
        );
    }

    /**
     * Adds the specified duration to this date-time object, returning a new instance with the added duration.
     *
     * @throws Exception\UnderflowException If adding the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the duration results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plus(Duration $duration): static
    {
        return static::fromTimestamp($this->timezone, $this->getTimestamp()->plus($duration));
    }

    /**
     * Subtracts the specified duration from this date-time object, returning a new instance with the subtracted duration.
     *
     * @throws Exception\UnderflowException If subtracting the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the duration results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minus(Duration $duration): static
    {
        return static::fromTimestamp($this->timezone, $this->getTimestamp()->minus($duration));
    }

    /**
     * Converts the date and time to the specified timezone.
     *
     * @param Timezone $timezone The timezone to convert to.
     *
     * @mutation-free
     */
    public function convertToTimezone(Timezone $timezone): static
    {
        return static::fromTimestamp($timezone, $this->getTimestamp());
    }

    public function jsonSerialize(): array
    {
        return [
            'timezone' => $this->timezone,
            'timestamp' => $this->timestamp,
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'seconds' => $this->seconds,
            'nanoseconds' => $this->nanoseconds,
        ];
    }
}
