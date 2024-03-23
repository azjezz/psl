<?php

declare(strict_types=1);

namespace Psl\DateTime;

use IntlDateFormatter;
use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Locale;
use Psl\Math;
use Psl\Str;

use function hrtime;
use function microtime;

/**
 * Represents a precise point in time, with seconds and nanoseconds since the Unix epoch.
 *
 * @immutable
 */
final class Timestamp implements TemporalInterface
{
    use TemporalConvenienceMethodsTrait;

    /**
     * @var null|array{int, int}
     *
     * @internal
     */
    private static ?array $offset = null;

    /**
     * @param int $seconds
     * @param int<0, 999999999> $nanoseconds
     *
     * @pure
     */
    private function __construct(
        private readonly int $seconds,
        private readonly int $nanoseconds,
    ) {
    }

    /**
     * Create a high-precision instance representing the current time using the system clock.
     */
    public static function now(): self
    {
        $time = microtime();

        $parts = Str\split($time, ' ');
        $seconds = (int) $parts[1];
        $nanoseconds = (int) (((float)$parts[0]) * ((float)NANOSECONDS_PER_SECOND));

        /** @psalm-suppress MissingThrowsDocblock */
        return self::fromRaw(
            seconds: $seconds,
            nanoseconds: $nanoseconds,
        );
    }

    /**
     * Create a current time instance using a monotonic clock with high precision
     *  to the nanosecond for precise measurements.
     *
     * This method ensures that the time is always moving forward, unaffected by adjustments in the system clock,
     * making it suitable for measuring durations or intervals accurately.
     *
     * @throws InvariantViolationException If the system does not provide a monotonic timer.
     */
    public static function monotonic(): self
    {
        if (self::$offset === null) {
            $offset = hrtime();

            /** @psalm-suppress RedundantCondition - This is not redundant, hrtime can return false. */
            Psl\invariant(false !== $offset, 'The system does not provide a monotonic timer.');

            $time = Str\split(microtime(), ' ', 2);

            self::$offset = [
                (int) ($time[1] - $offset[0]),
                (int) ($time[0] * NANOSECONDS_PER_SECOND) - $offset[1],
            ];
        }

        [$seconds, $nanoseconds] = hrtime();
        [$seconds_offset, $nanoseconds_offset] = self::$offset;

        $nanoseconds_adjusted = $nanoseconds + $nanoseconds_offset;
        if ($nanoseconds_adjusted >= NANOSECONDS_PER_SECOND) {
            ++$seconds;
            $nanoseconds_adjusted -= NANOSECONDS_PER_SECOND;
        } elseif ($nanoseconds_adjusted < 0) {
            --$seconds;
            $nanoseconds_adjusted += NANOSECONDS_PER_SECOND;
        }

        $seconds += $seconds_offset;
        $nanoseconds = $nanoseconds_adjusted;

        /** @psalm-suppress MissingThrowsDocblock */
        return self::fromRaw($seconds, $nanoseconds);
    }

    /**
     * Creates a timestamp from seconds and nanoseconds since the epoch.
     *
     * Normalizes so nanoseconds are within 0-999999999. For instance:
     * - `fromRaw(42, -100)` becomes (41, 999999900).
     * - `fromRaw(-42, -100)` becomes (-43, 999999900).
     * - `fromRaw(42, 1000000100)` becomes (43, 100).
     *
     * @param int $seconds Seconds since the epoch.
     * @param int $nanoseconds Additional nanoseconds to adjust by.
     *
     * @throws Exception\OverflowException
     * @throws Exception\UnderflowException
     *
     * @pure
     */
    public static function fromRaw(int $seconds, int $nanoseconds = 0): Timestamp
    {
        // Check for potential overflow or underflow before doing any operation
        if ($seconds === Math\INT64_MAX && $nanoseconds > 0) {
            throw new Exception\OverflowException("Adding nanoseconds would cause an overflow.");
        }

        if ($seconds === Math\INT64_MIN && $nanoseconds < 0) {
            throw new Exception\UnderflowException("Subtracting nanoseconds would cause an underflow.");
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $seconds_adjustment = Math\div($nanoseconds, NANOSECONDS_PER_SECOND);
        $adjusted_seconds = $seconds + $seconds_adjustment;

        $adjusted_nanoseconds = $nanoseconds % NANOSECONDS_PER_SECOND;
        if ($adjusted_nanoseconds < 0) {
            --$adjusted_seconds;
            $adjusted_nanoseconds += NANOSECONDS_PER_SECOND;
        }

        return new self($adjusted_seconds, $adjusted_nanoseconds);
    }

    /**
     * Creates a {@see Timestamp} instance from a date/time string according to a specific pattern.
     *
     * This method allows parsing of date/time strings that conform to custom patterns,
     * making it versatile for handling various date/time formats.
     *
     * @throws Exception\RuntimeException If parsing fails or the date/time string is invalid.
     *
     * @pure
     */
    public static function fromPattern(string|DatePattern $pattern, string $raw_string, ?Timezone $timezone = null, ?Locale\Locale $locale = null): self
    {
        $pattern = $pattern instanceof DatePattern ? $pattern->value : $pattern;

        /** @psalm-suppress ImpureMethodCall */
        $formatter = new IntlDateFormatter(
            $locale?->value,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $timezone === null ? null : Internal\to_intl_timezone($timezone),
            IntlDateFormatter::GREGORIAN,
            $pattern,
        );

        /** @psalm-suppress ImpureMethodCall */
        $timestamp = $formatter->parse($raw_string, $offset);
        if ($timestamp === false) {
            throw new Exception\RuntimeException(
                "Parsing error: Unable to interpret '$raw_string' as a valid date/time using pattern '$pattern'.",
            );
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return self::fromRaw((int) $timestamp);
    }

    /**
     * Parses a date/time string into a {@see Timestamp} instance.
     *
     * This method is a convenience wrapper for parsing date/time strings without specifying a custom pattern.
     *
     * @throws Exception\RuntimeException If parsing fails or the format of the date/time string is invalid.
     *
     * @pure
     */
    public static function parse(string $raw_string, ?Timezone $timezone = null, ?Locale\Locale $locale = null): self
    {
        /** @psalm-suppress ImpureMethodCall */
        $formatter = new IntlDateFormatter(
            $locale?->value,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $timezone === null ? null : Internal\to_intl_timezone($timezone),
            IntlDateFormatter::GREGORIAN,
        );

        /** @psalm-suppress ImpureMethodCall */
        $timestamp = $formatter->parse($raw_string);
        if ($timestamp === false) {
            throw new Exception\RuntimeException(
                "Parsing error: Unable to interpret '$raw_string' as a valid date/time.",
            );
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return self::fromRaw((int) $timestamp);
    }

    /**
     * Returns this Timestamp instance itself, as it already represents a timestamp.
     *
     * @mutation-free
     */
    public function getTimestamp(): self
    {
        return $this;
    }

    /**
     * Returns the raw seconds and nanoseconds of the timestamp as an array.
     *
     * @return array{int, int<0, 999999999>}
     *
     * @mutation-free
     */
    public function toRaw(): array
    {
        return [$this->seconds, $this->nanoseconds];
    }

    /**
     * Returns the number of seconds since the Unix epoch represented by this timestamp.
     *
     * @return int Seconds since the epoch. Can be negative for times before the epoch.
     *
     * @mutation-free
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Returns the nanoseconds part of this timestamp.
     *
     * @return int<0, 999999999> The nanoseconds part, ranging from 0 to 999999999.
     *
     * @mutation-free
     */
    public function getNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * Adds the specified duration to this timestamp object, returning a new instance with the added duration.
     *
     * @throws Exception\UnderflowException If adding the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the duration results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plus(Duration $duration): static
    {
        [$h, $m, $s, $ns] = $duration->getParts();
        $totalSeconds = SECONDS_PER_MINUTE * $m + SECONDS_PER_HOUR * $h + $s;
        $newSeconds = $this->seconds + $totalSeconds;
        $newNanoseconds = $this->nanoseconds + $ns;

        // No manual normalization required here due to fromRaw handling it
        return self::fromRaw($newSeconds, $newNanoseconds);
    }

    /**
     * Subtracts the specified duration from this timestamp object, returning a new instance with the subtracted duration.
     *
     * @throws Exception\UnderflowException If subtracting the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the duration results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minus(Duration $duration): static
    {
        [$h, $m, $s, $ns] = $duration->getParts();
        $totalSeconds = SECONDS_PER_MINUTE * $m + SECONDS_PER_HOUR * $h + $s;
        $newSeconds = $this->seconds - $totalSeconds;
        $newNanoseconds = $this->nanoseconds - $ns;

        // No manual normalization required here due to fromRaw handling it
        return self::fromRaw($newSeconds, $newNanoseconds);
    }

    /**
     * Formats the date and time of this instance into a string based on the provided pattern, timezone, and locale.
     *
     * If no pattern is specified, a default pattern will be used.
     *
     * If no timezone is specified, {@see Timezone::UTC} will be used.
     *
     * The method also accounts for locale-specific formatting rules if a locale is provided.
     *
     * @mutation-free
     *
     * @note The default pattern is subject to change at any time and should not be relied upon for consistent formatting.
     */
    public function format(null|DatePattern|string $pattern = null, ?Timezone $timezone = null, ?Locale\Locale $locale = null): string
    {
        if ($pattern instanceof DatePattern) {
            $pattern = $pattern->value;
        }

        $formatter = new IntlDateFormatter(
            $locale?->value,
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            $timezone === null ? null : Internal\to_intl_timezone($timezone),
            IntlDateFormatter::GREGORIAN,
            $pattern,
        );

        $result = $formatter->format($this->getSeconds());
        if (Str\starts_with($timezone->value, '+')) {
            $result = Str\replace($result, 'GMT+', 'UTC+');
        } elseif (Str\starts_with($timezone->value, '-')) {
            $result = Str\replace($result, 'GMT-', 'UTC-');
        }

        return $result;
    }

    public function jsonSerialize(): array
    {
        return [
            'seconds' => $this->seconds,
            'nanoseconds' => $this->nanoseconds,
        ];
    }
}
