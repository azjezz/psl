<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Meridiem;

final class MeridiemTest extends TestCase
{
    use DateTimeTestTrait;

    public function provideFromHourData(): iterable
    {
        yield [0, Meridiem::AnteMeridiem];
        yield [1, Meridiem::AnteMeridiem];
        yield [11, Meridiem::AnteMeridiem];
        yield [12, Meridiem::PostMeridiem];
        yield [23, Meridiem::PostMeridiem];
        yield [14, Meridiem::PostMeridiem];
    }

    /**
     * @dataProvider provideFromHourData
     */
    public function testFromHour(int $hour, Meridiem $expected): void
    {
        static::assertSame($expected, Meridiem::fromHour($hour));
    }

    public function testToggle(): void
    {
        static::assertSame(Meridiem::PostMeridiem, Meridiem::AnteMeridiem->toggle());
        static::assertSame(Meridiem::AnteMeridiem, Meridiem::PostMeridiem->toggle());
    }
}
