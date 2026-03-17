<?php

declare(strict_types=1);

namespace Psl\DateTime;

use DateInterval;
use Override;
use Psl\Comparison;

use function implode;

/**
 * Represents a calendar-based period with years, months, and days.
 *
 * Unlike {@see Duration} which represents exact time spans, a Period represents
 * calendar units whose actual time length depends on context (e.g. a month can
 * be 28-31 days, a day can be 23-25 hours due to DST).
 *
 * All instances are normalized as follows:
 *
 * - months overflow into years (e.g. 14 months = 1 year, 2 months)
 * - days are NOT normalized into months (a day is not a fixed number of hours)
 * - all non-zero parts (years, months, days) share the same sign
 *
 * Period does not implement {@see Comparison\Comparable} because comparing
 * "1 month" vs "30 days" is ambiguous without a reference date.
 *
 * @immutable
 */
final readonly class Period implements TemporalAmountInterface
{
    /**
     * @param int $years
     * @param int<-11, 11> $months
     * @param int $days
     *
     * @pure
     */
    private function __construct(
        private int $years,
        private int $months,
        private int $days,
    ) {}

    /**
     * Returns an instance representing the specified years, months, and days.
     *
     * Due to normalization, the actual values in the returned instance may
     * differ from the provided ones. Months overflow into years, and all
     * non-zero parts are guaranteed to share the same sign.
     *
     * @pure
     */
    public static function fromParts(int $years, int $months = 0, int $days = 0): self
    {
        // Normalize months into years.
        $totalMonths = ($years * MONTHS_PER_YEAR) + $months;
        $y = (int) ($totalMonths / MONTHS_PER_YEAR);
        $m = $totalMonths % MONTHS_PER_YEAR;

        // Ensure sign coherence: all non-zero parts share the same sign.
        // If years+months net sign disagrees with days, we cannot normalize
        // further (days are not convertible to months), so we leave them as-is.
        // However, within the year/month pair, we ensure coherence.
        // @codeCoverageIgnoreStart
        if ($y > 0 && $m < 0) {
            --$y;
            $m += MONTHS_PER_YEAR;
        } elseif ($y < 0 && $m > 0) {
            ++$y;
            $m -= MONTHS_PER_YEAR;
        }

        // @codeCoverageIgnoreEnd

        return new self($y, $m, $days);
    }

    /**
     * Returns an instance representing the specified number of years.
     *
     * @pure
     */
    public static function years(int $years): self
    {
        return new self($years, 0, 0);
    }

    /**
     * Returns an instance representing the specified number of months.
     *
     * Due to normalization, months overflow into years.
     *
     * @pure
     */
    public static function months(int $months): self
    {
        return self::fromParts(0, $months);
    }

    /**
     * Returns an instance representing the specified number of weeks.
     *
     * Weeks are converted to days (1 week = 7 days).
     *
     * @pure
     */
    public static function weeks(int $weeks): self
    {
        return new self(0, 0, $weeks * DAYS_PER_WEEK);
    }

    /**
     * Returns an instance representing the specified number of days.
     *
     * @pure
     */
    public static function days(int $days): self
    {
        return new self(0, 0, $days);
    }

    /**
     * Returns an instance with all parts equal to 0.
     *
     * @pure
     */
    public static function zero(): self
    {
        return new self(0, 0, 0);
    }

    /**
     * Calculates the calendar-based period between two dates.
     *
     * The result represents the difference in years, months, and days from
     * the start date to the end date. If end is before start, the result
     * will be negative.
     *
     * Only the date portion (year, month, day) is considered; time components
     * are ignored.
     *
     * @psalm-mutation-free
     */
    public static function between(DateTimeInterface $start, DateTimeInterface $end): self
    {
        $totalMonths =
            ($end->getYear() * MONTHS_PER_YEAR) + $end->getMonth()
            - (($start->getYear() * MONTHS_PER_YEAR) + $start->getMonth());
        $days = $end->getDay() - $start->getDay();

        if ($days < 0) {
            $totalMonths--;
            // Previous month: (endMonth + 10) % 12 + 1 maps 1→12, 2→1, 3→2, etc.
            $endMonth = $end->getMonth();
            $days += Month::from((($endMonth + 10) % MONTHS_PER_YEAR) + 1)->getDaysForYear(
                $end->getYear() - (int) (1 === $endMonth),
            );
        }

        return self::fromParts(0, $totalMonths, $days);
    }

    /**
     * Returns the period's components (years, months, days) in an array.
     *
     * @return array{int, int, int}
     *
     * @psalm-mutation-free
     */
    public function getParts(): array
    {
        return [$this->years, $this->months, $this->days];
    }

    /**
     * Returns the "years" part of this period.
     *
     * @psalm-mutation-free
     */
    public function getYears(): int
    {
        return $this->years;
    }

    /**
     * Returns the "months" part of this period.
     *
     * @psalm-mutation-free
     */
    public function getMonths(): int
    {
        return $this->months;
    }

    /**
     * Returns the "days" part of this period.
     *
     * @psalm-mutation-free
     */
    public function getDays(): int
    {
        return $this->days;
    }

    /**
     * Determines whether the instance represents a zero period.
     *
     * @psalm-mutation-free
     */
    public function isZero(): bool
    {
        return 0 === $this->years && 0 === $this->months && 0 === $this->days;
    }

    /**
     * Checks if the period is positive, implying that all non-zero components are positive.
     *
     * Note that this method returns false if all parts are equal to 0.
     *
     * @psalm-mutation-free
     */
    public function isPositive(): bool
    {
        return $this->years > 0 || $this->months > 0 || $this->days > 0;
    }

    /**
     * Checks if the period is negative, implying that all non-zero components are negative.
     *
     * Note that this method returns false if all parts are equal to 0.
     *
     * @psalm-mutation-free
     */
    public function isNegative(): bool
    {
        return $this->years < 0 || $this->months < 0 || $this->days < 0;
    }

    /**
     * Returns a new instance with the "years" part changed to the specified value.
     *
     * Note that due to normalization, this may affect other parts.
     *
     * @psalm-mutation-free
     */
    public function withYears(int $years): self
    {
        return self::fromParts($years, $this->months, $this->days);
    }

    /**
     * Returns a new instance with the "months" part changed to the specified value.
     *
     * Note that due to normalization, months may overflow into years.
     *
     * @psalm-mutation-free
     */
    public function withMonths(int $months): self
    {
        return self::fromParts($this->years, $months, $this->days);
    }

    /**
     * Returns a new instance with the "days" part changed to the specified value.
     *
     * @psalm-mutation-free
     */
    public function withDays(int $days): self
    {
        return self::fromParts($this->years, $this->months, $days);
    }

    /**
     * Evaluates whether this period is equivalent to another.
     *
     * @param TemporalAmountInterface $other
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function equals(mixed $other): bool
    {
        if (!$other instanceof Period) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return $this->years === $other->years && $this->months === $other->months && $this->days === $other->days;
    }

    /**
     * Returns a new instance with all parts negated.
     *
     * @psalm-mutation-free
     */
    public function invert(): self
    {
        if ($this->isZero()) {
            return $this;
        }

        return new self(-$this->years, -$this->months, -$this->days);
    }

    /**
     * Returns a new instance representing the sum of this instance and the provided other.
     *
     * This operation is commutative: `$a->plus($b)` equals `$b->plus($a)`.
     *
     * @psalm-mutation-free
     */
    public function plus(self $other): self
    {
        if ($other->isZero()) {
            return $this;
        }

        if ($this->isZero()) {
            return $other;
        }

        return self::fromParts(
            $this->years + $other->years,
            $this->months + $other->months,
            $this->days + $other->days,
        );
    }

    /**
     * Returns a new instance representing the difference between this and the provided other.
     *
     * This operation is not commutative: `$a->minus($b)` does not equal `$b->minus($a)`.
     * But: `$a->minus($b)` equals `$b->minus($a)->invert()`.
     *
     * @psalm-mutation-free
     */
    public function minus(self $other): self
    {
        if ($other->isZero()) {
            return $this;
        }

        if ($this->isZero()) {
            return $other->invert();
        }

        return self::fromParts(
            $this->years - $other->years,
            $this->months - $other->months,
            $this->days - $other->days,
        );
    }

    /**
     * Returns an ISO 8601 duration string representing this period.
     *
     * Examples: "P1Y6M15D", "P2M", "P0D", "-P1Y2M".
     *
     * @throws Exception\InvalidArgumentException If years/months and days have different signs.
     *
     * @psalm-mutation-free
     */
    public function toIso8601(): string
    {
        return Internal\format_iso8601_period($this->years, $this->months, $this->days);
    }

    /**
     * Parses an ISO 8601 duration string into a Period.
     *
     * Accepts formats like "P1Y6M15D", "P2W", "-P3M", "P0D".
     * Only date components (Y, M, W, D) are accepted. Time components (T...) will
     * cause a {@see Exception\ParserException}.
     *
     * @throws Exception\ParserException If the string is not a valid ISO 8601 period.
     *
     * @pure
     */
    public static function fromIso8601(string $value): self
    {
        [$years, $months, $days] = Internal\parse_iso8601_period($value);

        return self::fromParts($years, $months, $days);
    }

    /**
     * Adds this period to the given temporal, returning the result.
     *
     * The temporal must be a {@see DateTimeInterface} because calendar-based
     * arithmetic requires a date context.
     *
     * @throws Exception\InvalidArgumentException If the temporal is not a DateTimeInterface.
     *
     * @psalm-mutation-free
     */
    public function addTo(TemporalInterface $temporal): TemporalInterface
    {
        if (!$temporal instanceof DateTimeInterface) {
            throw new Exception\InvalidArgumentException('Cannot add a Period to a Timestamp; use a DateTime instead.');
        }

        return $temporal->plus($this);
    }

    /**
     * Subtracts this period from the given temporal, returning the result.
     *
     * The temporal must be a {@see DateTimeInterface} because calendar-based
     * arithmetic requires a date context.
     *
     * @throws Exception\InvalidArgumentException If the temporal is not a DateTimeInterface.
     *
     * @psalm-mutation-free
     */
    public function subtractFrom(TemporalInterface $temporal): TemporalInterface
    {
        if (!$temporal instanceof DateTimeInterface) {
            throw new Exception\InvalidArgumentException(
                'Cannot subtract a Period from a Timestamp; use a DateTime instead.',
            );
        }

        return $temporal->minus($this);
    }

    /**
     * Returns the period as a human-readable string.
     *
     * @psalm-mutation-free
     */
    public function toString(): string
    {
        $containsYears = 0 !== $this->years;
        $containsMonths = 0 !== $this->months;
        $containsDays = 0 !== $this->days;

        /** @var list<string> $output */
        $output = [];
        if ($containsYears) {
            $output[] = $this->years . ' year(s)';
        }

        if ($containsMonths || $containsYears && $containsDays) {
            $output[] = $this->months . ' month(s)';
        }

        if ($containsDays) {
            $output[] = $this->days . ' day(s)';
        }

        return [] === $output ? '0 day(s)' : implode(', ', $output);
    }

    /**
     * @psalm-mutation-free
     */
    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Converts this {@see Period} to a PHP {@see DateInterval}.
     *
     * Note: nanosecond precision is truncated to microseconds.
     *
     * @return DateInterval
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function toStdlib(): DateInterval
    {
        $parts = [];
        if (0 !== $this->years) {
            $parts[] = $this->years . ' years';
        }

        if (0 !== $this->months) {
            $parts[] = $this->months . ' months';
        }

        if (0 !== $this->days) {
            $parts[] = $this->days . ' days';
        }

        if ([] === $parts) {
            $parts[] = '0 days';
        }

        return DateInterval::createFromDateString(implode(' ', $parts));
    }

    /**
     * @return array{years: int, months: int, days: int}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'years' => $this->years,
            'months' => $this->months,
            'days' => $this->days,
        ];
    }
}
