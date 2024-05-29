<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;

use function Psl\DateTime\is_leap_year;

final class IsLeapYearTest extends TestCase
{
    use DateTimeTestTrait;

    public function provideLeapYearData(): iterable
    {
        yield [2024, true];
        yield [2000, true];
        yield [1900, false];
        yield [2023, false];
        yield [2020, true];
        yield [2100, false];
        yield [2400, true];
        yield [1996, true];
        yield [1999, false];
    }

    /**
     * @dataProvider provideLeapYearData
     */
    public function testIsLeapYear(int $year, bool $expected): void
    {
        static::assertSame($expected, is_leap_year($year));
    }
}
