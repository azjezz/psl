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
        static::assertSame(0, $today->getNanoseconds());
    }

    public function testTodayAtDefaults(): void
    {
        $now = DateTime::now();
        $today = DateTime::todayAt(14, 0);

        static::assertSame($now->getDate(), $today->getDate());
        static::assertNotSame($now->getTime(), $today->getTime());
        static::assertSame(14, $today->getHours());
        static::assertSame(0, $today->getMinutes());
        static::assertSame(0, $today->getSeconds());
        static::assertSame(0, $today->getNanoseconds());
        static::assertSame(Timezone::default(), $today->getTimezone());
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

    public function testFromPartsWithDefaults(): void
    {
        $datetime = DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4, );

        static::assertSame(Timezone::UTC, $datetime->getTimezone());
        static::assertSame(2024, $datetime->getYear());
        static::assertSame(2, $datetime->getMonth());
        static::assertSame(4, $datetime->getDay());
        static::assertSame(Weekday::Sunday, $datetime->getWeekday());
        static::assertSame(0, $datetime->getHours());
        static::assertSame(0, $datetime->getMinutes());
        static::assertSame(0, $datetime->getSeconds());
        static::assertSame(0, $datetime->getNanoseconds());
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
        static::assertSame($datetime->getTimezone(), $parsed->getTimezone());
    }

    public function testParseWithTimezone(): void
    {
        $datetime = DateTime::fromParts(Timezone::AmericaNewYork, 2024, Month::February, 4, 14, 0, 0, 0);

        $string = $datetime->format();
        $parsed = DateTime::parse($string, timezone: TimeZone::AmericaNewYork);

        static::assertEquals($datetime->getTimestamp(), $parsed->getTimestamp());
        static::assertSame($datetime->getTimezone(), $parsed->getTimezone());

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

    public function testPlusMonthsEdgeCases(): void
    {
        $jan_31th = DateTime::fromParts(Timezone::default(), 2024, Month::January, 31, 14, 0, 0, 0);
        $febr_29th = $jan_31th->plusMonths(1);
        static::assertSame([2024, 2, 29], $febr_29th->getDate());
        static::assertSame([14, 0, 0, 0], $febr_29th->getTime());

        $dec_31th = DateTime::fromParts(Timezone::default(), 2023, Month::December, 31, 14, 0, 0, 0);
        $march_31th = $dec_31th->plusMonths(3);
        static::assertSame([2024, 3, 31], $march_31th->getDate());
        static::assertSame([14, 0, 0, 0], $march_31th->getTime());

        $april_30th = $march_31th->plusMonths(1);
        static::assertSame([2024, 4, 30], $april_30th->getDate());
        static::assertSame([14, 0, 0, 0], $april_30th->getTime());

        $april_30th_next_year = $april_30th->plusYears(1);
        static::assertSame([2025, 4, 30], $april_30th_next_year->getDate());
        static::assertSame([14, 0, 0, 0], $april_30th_next_year->getTime());
    }

    public function testPlusMonthOverflows(): void
    {
        $jan_31th_2024 = DateTime::fromParts(Timezone::default(), 2024, Month::January, 31, 14, 0, 0, 0);
        $previous_month = 1;
        for ($i = 1; $i < 24; $i++) {
            $res = $jan_31th_2024->plusMonths($i);

            $expected_month = ($previous_month + 1) % 12;
            $expected_month = $expected_month === 0 ? 12 : $expected_month;

            static::assertSame($res->getDay(), $res->getMonthEnum()->getDaysForYear($res->getYear()));
            static::assertSame($res->getMonth(), $expected_month);

            $previous_month = $expected_month;
        }
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

    public function testMinusMonthsEdgeCases(): void
    {
        $febr_29th = DateTime::fromParts(Timezone::default(), 2024, Month::February, 29, 14, 0, 0, 0);
        $jan_29th = $febr_29th->minusMonths(1);
        static::assertSame([2024, 1, 29], $jan_29th->getDate());
        static::assertSame([14, 0, 0, 0], $jan_29th->getTime());

        $febr_28th_previous_year = $febr_29th->minusYears(1);
        static::assertSame([2023, 2, 28], $febr_28th_previous_year->getDate());
        static::assertSame([14, 0, 0, 0], $febr_28th_previous_year->getTime());

        $febr_29th_previous_leap_year = $febr_29th->minusYears(4);
        static::assertSame([2020, 2, 29], $febr_29th_previous_leap_year->getDate());
        static::assertSame([14, 0, 0, 0], $febr_29th_previous_leap_year->getTime());

        $march_31th = DateTime::fromParts(Timezone::default(), 2024, Month::March, 31, 14, 0, 0, 0);
        $dec_31th = $march_31th->minusMonths(3);
        static::assertSame([2023, 12, 31], $dec_31th->getDate());
        static::assertSame([14, 0, 0, 0], $dec_31th->getTime());

        $jan_31th = $march_31th->minusMonths(2);
        static::assertSame([2024, 1, 31], $jan_31th->getDate());
        static::assertSame([14, 0, 0, 0], $jan_31th->getTime());

        $may_31th = DateTime::fromParts(Timezone::default(), 2024, Month::May, 31, 14, 0, 0, 0);
        $april_30th = $may_31th->minusMonths(1);
        static::assertSame([2024, 4, 30], $april_30th->getDate());
        static::assertSame([14, 0, 0, 0], $april_30th->getTime());

        $april_30th_previous_year = $april_30th->minusYears(1);
        static::assertSame([2023, 4, 30], $april_30th_previous_year->getDate());
        static::assertSame([14, 0, 0, 0], $april_30th_previous_year->getTime());
    }

    public function testMinusMonthOverflows(): void
    {
        $jan_31th_2024 = DateTime::fromParts(Timezone::default(), 2024, Month::January, 31, 14, 0, 0, 0);
        $previous_month = 1;
        for ($i = 1; $i < 24; $i++) {
            $res = $jan_31th_2024->minusMonths($i);

            $expected_month = $previous_month - 1;
            $expected_month = $expected_month === 0 ? 12 : $expected_month;

            static::assertSame($res->getDay(), $res->getMonthEnum()->getDaysForYear($res->getYear()));
            static::assertSame($res->getMonth(), $expected_month);

            $previous_month = $expected_month;
        }
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

    public function testWithTime()
    {
        $date = DateTime::todayAt(14, 0);
        $new = $date->withTime(15, 0);

        self::assertSame(15, $new->getHours());
        self::assertSame(0, $new->getMinutes());
        self::assertSame(0, $new->getSeconds());
        self::assertSame(0, $new->getNanoseconds());
    }
}
