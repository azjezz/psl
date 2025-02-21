<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\DateStyle;
use Psl\DateTime\DateTime;
use Psl\DateTime\Exception\UnexpectedValueException;
use Psl\DateTime\FormatPattern;
use Psl\DateTime\Meridiem;
use Psl\DateTime\Month;
use Psl\DateTime\TimeStyle;
use Psl\DateTime\Timezone;
use Psl\DateTime\Weekday;
use Psl\Json;
use Psl\Locale\Locale;

use function Psl\DateTime\Internal\create_intl_date_formatter;
use function time;

/**
 * @mago-ignore php-unit/strict-assertions
 */
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
        $today = DateTime::todayAt(14, 0o0, 0o0);

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
        static::assertSame(24, $datetime->getYearShort());
        static::assertSame(2, $datetime->getMonth());
        static::assertSame(4, $datetime->getDay());
        static::assertSame(Weekday::Sunday, $datetime->getWeekday());
        static::assertSame(14, $datetime->getHours());
        static::assertSame(0, $datetime->getMinutes());
        static::assertSame(0, $datetime->getSeconds());
        static::assertSame(1, $datetime->getNanoseconds());
        static::assertSame([2024, 2, 4, 14, 0, 0, 1], $datetime->getParts());
    }

    public function testFromPartsWithDefaults(): void
    {
        $datetime = DateTime::fromParts(Timezone::UTC, 2024, Month::February, 4);

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

    /**
     * @dataProvider provideInvalidComponentParts
     */
    public function testFromPartsWithInvalidComponent(
        string $expectedMessage,
        int $year,
        int $month,
        int $day,
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
    ): void {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($expectedMessage);

        DateTime::fromParts(Timezone::UTC, $year, $month, $day, $hours, $minutes, $seconds, $nanoseconds);
    }

    public static function provideInvalidComponentParts(): array
    {
        return [
            [
                'Unexpected year value encountered. Provided "0", but the calendar expects "1". Check the year for accuracy and ensure it\'s within the supported range.',
                0,
                1,
                1,
                0,
                0,
                0,
                0,
            ],
            [
                'Unexpected month value encountered. Provided "0", but the calendar expects "12". Ensure the month is within the 1-12 range and matches the specific year context.',
                2024,
                0,
                1,
                0,
                0,
                0,
                0,
            ],
            [
                'Unexpected day value encountered. Provided "0", but the calendar expects "31". Ensure the day is valid for the given month and year, considering variations like leap years.',
                2024,
                1,
                0,
                0,
                0,
                0,
                0,
            ],
            [
                'Unexpected hours value encountered. Provided "-1", but the calendar expects "23". Ensure the hour falls within a 24-hour day.',
                2024,
                1,
                1,
                -1,
                0,
                0,
                0,
            ],
            [
                'Unexpected minutes value encountered. Provided "-1", but the calendar expects "59". Check the minutes value for errors and ensure it\'s within the 0-59 range.',
                2024,
                1,
                1,
                0,
                -1,
                0,
                0,
            ],
            [
                'Unexpected seconds value encountered. Provided "59", but the calendar expects "-1". Ensure the seconds are correct and within the 0-59 range.',
                2024,
                1,
                1,
                0,
                0,
                -1,
                0,
            ],
        ];
    }

    public function testFromString(): void
    {
        $timezone = Timezone::EuropeBrussels;
        $datetime = DateTime::fromParts($timezone, 2024, Month::February, 4, 14, 0, 0, 0);

        $string = $datetime->toString();
        $parsed = DateTime::fromString($string, timezone: $timezone);

        static::assertEquals($datetime->getTimestamp(), $parsed->getTimestamp());
        static::assertSame($datetime->getTimezone(), $parsed->getTimezone());
        static::assertSame($string, $parsed->toString());
    }

    public function testToString(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertSame('4 Feb 2024, 14:00:00', $datetime->toString());
        static::assertSame('04/02/2024, 14:00:00', $datetime->toString(date_style: DateStyle::Short));
        static::assertSame(
            '4 Feb 2024, 14:00:00 Greenwich Mean Time',
            $datetime->toString(time_style: TimeStyle::Full),
        );
        static::assertSame('4 Feb 2024, 15:00:00', $datetime->toString(timezone: TimeZone::EuropeBrussels));

        // Formatting depends on version of intl - so compare with intl version instead of hardcoding a label:
        static::assertSame(
            create_intl_date_formatter(locale: Locale::DutchBelgium)->format($datetime->getTimestamp()->getSeconds()),
            $datetime->toString(locale: Locale::DutchBelgium),
        );
    }

    public function testFormat(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertSame('4 Feb 2024, 14:00:00', $datetime->format());
        static::assertSame('02/04/2024', $datetime->format(pattern: FormatPattern::American));
        static::assertSame('02/04/2024', $datetime->format(pattern: FormatPattern::American->value));
        static::assertSame('4 Feb 2024, 15:00:00', $datetime->format(timezone: TimeZone::EuropeBrussels));

        // Formatting depends on version of intl - so compare with intl version instead of hardcoding a label:
        static::assertSame(
            create_intl_date_formatter(locale: Locale::DutchBelgium)->format($datetime->getTimestamp()->getSeconds()),
            $datetime->toString(locale: Locale::DutchBelgium),
        );
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

    public function testGetEra(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, 14, 0, 0, 0);

        static::assertSame('AD', $datetime->getEra()->value);
    }

    public function testGetCentury(): void
    {
        static::assertSame(20, DateTime::fromParts(Timezone::default(), 1999, Month::February, 4, 14)->getCentury());
        static::assertSame(21, DateTime::fromParts(Timezone::default(), 2000, Month::February, 4, 14)->getCentury());
    }

    public static function provideTwelveHours(): iterable
    {
        yield [0, 12, Meridiem::AnteMeridiem];
        yield [1, 1, Meridiem::AnteMeridiem];
        yield [2, 2, Meridiem::AnteMeridiem];
        yield [11, 11, Meridiem::AnteMeridiem];
        yield [12, 12, Meridiem::PostMeridiem];
        yield [13, 1, Meridiem::PostMeridiem];
        yield [14, 2, Meridiem::PostMeridiem];
        yield [23, 11, Meridiem::PostMeridiem];
    }

    /**
     * @dataProvider provideTwelveHours
     */
    public function testGetTwelveHours(int $hour, $expectedTwelveHour, $expectedMeridiem): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::February, 4, $hour, 0, 0, 0);
        [$hours, $meridiem] = $datetime->getTwelveHours();

        static::assertSame($expectedTwelveHour, $hours);
        static::assertSame($expectedMeridiem, $meridiem);
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

        $new = $datetime->plusMonths(0);
        static::assertSame($datetime, $new);

        $new = $datetime->plusMonths(-1);
        static::assertSame(1, $new->getMonth());

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

        $new = $datetime->minusMonths(0);
        static::assertSame($datetime, $new);

        $new = $datetime->minusMonths(-1);
        static::assertSame(3, $new->getMonth());

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

    public function testWithTime(): void
    {
        $date = DateTime::todayAt(14, 0);
        $new = $date->withTime(15, 0);

        static::assertSame(15, $new->getHours());
        static::assertSame(0, $new->getMinutes());
        static::assertSame(0, $new->getSeconds());
        static::assertSame(0, $new->getNanoseconds());
    }

    public function testTimezoneInfo(): void
    {
        $timeZone = Timezone::EuropeBrussels;
        $date = DateTime::fromParts($timeZone, 2024, 0o1, 0o1);

        static::assertSame(!$timeZone->getDaylightSavingTimeOffset($date)->isZero(), $date->isDaylightSavingTime());
        static::assertEquals($timeZone->getOffset($date), $date->getTimezoneOffset());
    }

    public function testConvertTimeZone(): void
    {
        $date = DateTime::fromParts(Timezone::EuropeBrussels, 2024, 0o1, 0o1, 1);
        $converted = $date->convertToTimezone($london = Timezone::EuropeLondon);

        static::assertSame($london, $converted->getTimezone());
        static::assertSame($date->getTimestamp(), $converted->getTimestamp());
        static::assertSame($date->getYear(), $converted->getYear());
        static::assertSame($date->getMonth(), $converted->getMonth());
        static::assertSame($date->getDay(), $converted->getDay());
        static::assertSame(0, $converted->getHours());
    }
}
