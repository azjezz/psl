<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Month;

final class MonthTest extends TestCase
{
    use DateTimeTestTrait;

    /**
     * @dataProvider provideGetPreviousData
     */
    public function testGetPrevious(Month $month, Month $expected): void
    {
        static::assertSame($expected, $month->getPrevious());
    }

    /**
     * @dataProvider provideGetNextData
     */
    public function testGetNext(Month $month, Month $expected): void
    {
        static::assertSame($expected, $month->getNext());
    }

    /**
     * @dataProvider provideGetDaysData
     */
    public function testGetDays(Month $month, int $expectedForLeapYear, int $expectedForNonLeapYear): void
    {
        static::assertSame($expectedForLeapYear, $month->getLeapYearDays());
        static::assertSame($expectedForNonLeapYear, $month->getNonLeapYearDays());
    }

    /**
     * @dataProvider provideGetDaysForYearData
     */
    public function testGetDaysForYear(Month $month, int $year, int $expected): void
    {
        static::assertSame($expected, $month->getDaysForYear($year));
    }

    /**
     * @return iterable<array{Month, Month}>
     */
    public static function provideGetPreviousData(): iterable
    {
        yield [Month::January, Month::December];
        yield [Month::February, Month::January];
        yield [Month::March, Month::February];
        yield [Month::April, Month::March];
        yield [Month::May, Month::April];
        yield [Month::June, Month::May];
        yield [Month::July, Month::June];
        yield [Month::August, Month::July];
        yield [Month::September, Month::August];
        yield [Month::October, Month::September];
        yield [Month::November, Month::October];
        yield [Month::December, Month::November];
    }

    /**
     * @return iterable<array{Month, Month}>
     */
    public static function provideGetNextData(): iterable
    {
        yield [Month::January, Month::February];
        yield [Month::February, Month::March];
        yield [Month::March, Month::April];
        yield [Month::April, Month::May];
        yield [Month::May, Month::June];
        yield [Month::June, Month::July];
        yield [Month::July, Month::August];
        yield [Month::August, Month::September];
        yield [Month::September, Month::October];
        yield [Month::October, Month::November];
        yield [Month::November, Month::December];
        yield [Month::December, Month::January];
    }

    /**
     * @return iterable<array{Month, int, int}>
     */
    public static function provideGetDaysData(): iterable
    {
        yield [Month::January, 31, 31];
        yield [Month::February, 29, 28];
        yield [Month::March, 31, 31];
        yield [Month::April, 30, 30];
        yield [Month::May, 31, 31];
        yield [Month::June, 30, 30];
        yield [Month::July, 31, 31];
        yield [Month::August, 31, 31];
        yield [Month::September, 30, 30];
        yield [Month::October, 31, 31];
        yield [Month::November, 30, 30];
        yield [Month::December, 31, 31];
    }

    /**
     * @return iterable<array{Month, int, int}>
     */
    public static function provideGetDaysForYearData(): iterable
    {
        yield [Month::January, 2024, 31];
        yield [Month::February, 2024, 29];
        yield [Month::March, 2024, 31];
        yield [Month::April, 2024, 30];
        yield [Month::May, 2024, 31];
        yield [Month::June, 2024, 30];
        yield [Month::July, 2024, 31];
        yield [Month::August, 2024, 31];
        yield [Month::September, 2024, 30];
        yield [Month::October, 2024, 31];
        yield [Month::November, 2024, 30];
        yield [Month::December, 2024, 31];
    }
}
