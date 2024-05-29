<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\DateTime;
use Psl\DateTime\Exception\UnexpectedValueException;
use Psl\DateTime\Meridiem;
use Psl\DateTime\Month;
use Psl\DateTime\Timezone;
use Psl\DateTime\Weekday;
use Psl\Json;

use function time;

final class DateTimeTest extends TestCase
{
    use DateTimeTestTrait;

    public function testNow(): void
    {
        $timestamp = DateTime::now()->getTimestamp();

        static::assertEqualsWithDelta(time(), $timestamp->getSeconds(), 1);
    }

    public function testTodayAt(): void
    {
        $now = DateTime::now();
        $today = DateTime::todayAt(14, 00, 00);

        static::assertSame($now->getDate(), $today->getDate());
        static::assertNotSame($now->getTime(), $today->getTime());
        static::assertSame(14, $today->getHours());
        static::assertSame(0, $today->getMinutes());
        static::assertSame(0, $today->getSeconds());
    }

    public function testFromParts(): void
    {
        $datetime = DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4, 14, 0, 0, 1);

        static::assertSame(Timezone::UTC, $datetime->getTimezone());
        static::assertSame(2024, $datetime->getYear());
        static::assertSame(2, $datetime->getMonth());
        static::assertSame(4, $datetime->getDay());
        static::assertSame(Weekday::Sunday, $datetime->getWeekday());
        static::assertSame(14, $datetime->getHours());
        static::assertSame(0, $datetime->getMinutes());
        static::assertSame(0, $datetime->getSeconds());
        static::assertSame(1, $datetime->getNanoseconds());
    }

    public function testFromPartsWithInvalidComponent(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unexpected hours value encountered. Provided "999", but the calendar expects "15". Ensure the hour falls within a 24-hour day.');

        DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4, 999, 0, 0, 1);
    }

    public function fromString(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        $string = $datetime->toString();
        $parsed = DateTime::fromString($string);

        static::assertEquals($datetime->getTimestamp(), $parsed->getTimestamp());
    }

    public function testParse(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        $string = $datetime->format();
        $parsed = DateTime::parse($string);

        static::assertEquals($datetime->getTimestamp(), $parsed->getTimestamp());
    }

    public function testWithDate(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);
        $new = $datetime->withDate(2025, Month::March, 5);

        static::assertSame(2025, $new->getYear());
        static::assertSame(3, $new->getMonth());
        static::assertSame(5, $new->getDay());
        static::assertSame(14, $new->getHours());
        static::assertSame(0, $new->getMinutes());
        static::assertSame(0, $new->getSeconds());
        static::assertSame(0, $new->getNanoseconds());
    }

    public function testWithMethods(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        $new = $datetime->withYear(2025);
        static::assertSame(2025, $new->getYear());

        $new = $datetime->withMonth(Month::March);
        static::assertSame(3, $new->getMonth());

        $new = $datetime->withDay(5);
        static::assertSame(5, $new->getDay());

        $new = $datetime->withHours(15);
        static::assertSame(15, $new->getHours());

        $new = $datetime->withMinutes(30);
        static::assertSame(30, $new->getMinutes());

        $new = $datetime->withSeconds(45);
        static::assertSame(45, $new->getSeconds());

        $new = $datetime->withNanoseconds(100);
        static::assertSame(100, $new->getNanoseconds());
    }

    public function testGetEra()
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertSame('AD', $datetime->getEra()->value);
    }

    public function testGetCentury()
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertSame(21, $datetime->getCentury());
    }

    public function testGetTwelveHours()
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);
        [$hours, $meridiem] = $datetime->getTwelveHours();

        static::assertSame(2, $hours);
        static::assertSame(Meridiem::PostMeridiem, $meridiem);
    }

    public function testGetIsoWeek(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        [$year, $week] = $datetime->getISOWeekNumber();

        static::assertSame(2024, $year);
        static::assertSame(5, $week);

        $datetime = DateTime::fromParts(Timezone::default(), 2023, Month::January, 1, 14, 0, 0, 0);

        [$year, $week] = $datetime->getISOWeekNumber();

        static::assertSame(2022, $year);
        static::assertSame(52, $week);

        $datetime = DateTime::fromParts(Timezone::default(), 2025, Month::December, 31, 14, 0, 0, 0);

        [$year, $week] = $datetime->getISOWeekNumber();

        static::assertSame(2026, $year);
        static::assertSame(1, $week);
    }

    public function testPlusMethods(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        $new = $datetime->plusYears(1);
        static::assertSame(2025, $new->getYear());

        $new = $datetime->plusMonths(1);
        static::assertSame(3, $new->getMonth());

        $new = $datetime->plusDays(1);
        static::assertSame(5, $new->getDay());

        $new = $datetime->plusHours(1);
        static::assertSame(15, $new->getHours());

        $new = $datetime->plusMinutes(1);
        static::assertSame(1, $new->getMinutes());

        $new = $datetime->plusSeconds(1);
        static::assertSame(1, $new->getSeconds());

        $new = $datetime->plusNanoseconds(1);
        static::assertSame(1, $new->getNanoseconds());
    }

    public function testMinusMethods(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        $new = $datetime->minusYears(1);
        static::assertSame(2023, $new->getYear());

        $new = $datetime->minusMonths(1);
        static::assertSame(1, $new->getMonth());

        $new = $datetime->minusDays(1);
        static::assertSame(3, $new->getDay());

        $new = $datetime->minusHours(1);
        static::assertSame(13, $new->getHours());

        $new = $datetime->minusMinutes(1);
        static::assertSame(59, $new->getMinutes());

        $new = $datetime->minusSeconds(1);
        static::assertSame(59, $new->getSeconds());

        $new = $datetime->minusNanoseconds(1);
        static::assertSame(999999999, $new->getNanoseconds());
    }

    public function testIsLeapYear(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertTrue($datetime->isLeapYear());

        $datetime = DateTime::fromParts(Timezone::default(), 2023, Month::February, 4, 14, 0, 0, 0);

        static::assertFalse($datetime->isLeapYear());
    }

    public function testToRfc3999(): void
    {
        $datetime = DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertSame('2024-02-04T14:00:00+00:00', $datetime->toRfc3339());
    }

    public function testEqualIncludingTimezone(): void
    {
        $datetime1 = DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertTrue($datetime1->equals($datetime2));
        static::assertFalse($datetime1->equalsIncludingTimezone($datetime2));

        $datetime1 = DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertTrue($datetime1->equals($datetime2));
        static::assertTrue($datetime1->equalsIncludingTimezone($datetime2));

        $datetime1 = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertTrue($datetime1->equals($datetime2));
        static::assertTrue($datetime1->equalsIncludingTimezone($datetime2));

        $datetime1 = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 15, 0, 0, 0);

        static::assertFalse($datetime1->equals($datetime2));
        static::assertFalse($datetime1->equalsIncludingTimezone($datetime2));
    }

    public function testJsonSerialize(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertSame(
            '{"timezone":"Europe/London","timestamp":{"seconds":1707055200,"nanoseconds":0},"year":2024,"month":2,"day":4,"hours":14,"minutes":0,"seconds":0,"nanoseconds":0}',
            Json\encode($datetime),
        );
    }
}
