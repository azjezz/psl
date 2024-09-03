<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\DateTime;
use Psl\DateTime\Timestamp;
use Psl\DateTime\Timezone;

final class TimezoneTest extends TestCase
{
    use DateTimeTestTrait;

    public function testDefault(): void
    {
        /**
         * @see DateTimeTestTrait::setUp() for the default timezone set to Europe/London
         */
        static::assertSame(Timezone::EuropeLondon, Timezone::default());
    }

    public function testGetOffset(): void
    {
        $temporal = Timestamp::fromParts(seconds: 1716956903);

        static::assertSame(3600., Timezone::EuropeLondon->getOffset($temporal)->getTotalSeconds());
        static::assertSame(-14400., Timezone::AmericaNewYork->getOffset($temporal)->getTotalSeconds());
        static::assertSame(28800., Timezone::AsiaShanghai->getOffset($temporal)->getTotalSeconds());
        static::assertSame(12600., Timezone::Plus0330->getOffset($temporal)->getTotalSeconds());
        static::assertSame(-12600., Timezone::Minus0330->getOffset($temporal)->getTotalSeconds());
        static::assertSame(3600., Timezone::Plus0100->getOffset($temporal)->getTotalSeconds());
        static::assertSame(-3600., Timezone::Minus0100->getOffset($temporal)->getTotalSeconds());

        // Local
        $brussels = Timezone::EuropeBrussels;
        date_default_timezone_set($brussels->value);

        $summer = DateTime::fromParts($brussels, 2024, 3, 31, 3);

        static::assertSame(2., $brussels->getOffset($summer)->getTotalHours());
        static::assertSame(1., $brussels->getOffset($summer, local: true)->getTotalHours());
    }

    /**
     * @dataProvider provideRawOffsetData
     */
    public function testRawOffset(Timezone $timezone, int $expected): void
    {
        static::assertSame($expected, (int) $timezone->getRawOffset()->getTotalSeconds());
    }

    public function testUsesDaylightSavingTime(): void
    {
        static::assertTrue(Timezone::AmericaNewYork->usesDaylightSavingTime());
        static::assertTrue(Timezone::EuropeLondon->usesDaylightSavingTime());
        static::assertFalse(Timezone::AsiaShanghai->usesDaylightSavingTime());
    }

    public function testGetDaylightSavingTimeSavings(): void
    {
        static::assertSame(3600., Timezone::AmericaNewYork->getDaylightSavingTimeSavings()->getTotalSeconds());
        static::assertSame(3600., Timezone::EuropeLondon->getDaylightSavingTimeSavings()->getTotalSeconds());
        static::assertSame(0., Timezone::AsiaShanghai->getDaylightSavingTimeSavings()->getTotalSeconds());
    }

    public function testHasTheSameRulesAs(): void
    {
        static::assertTrue(Timezone::AmericaNewYork->hasTheSameRulesAs(Timezone::AmericaNewYork));
        static::assertFalse(Timezone::AmericaNewYork->hasTheSameRulesAs(Timezone::EuropeLondon));
    }

    public function testGetDaylightSavingTimeOffset(): void
    {
        $brussels = Timezone::EuropeBrussels;
        date_default_timezone_set($brussels->value);

        $summer = DateTime::fromParts($brussels, 2024, 3, 31, 3);
        $winter = DateTime::fromParts($brussels, 2024, 10, 27, 2);

        static::assertSame(0., $brussels->getDaylightSavingTimeOffset($winter)->getTotalHours());
        static::assertSame(1., $brussels->getDaylightSavingTimeOffset($winter, local: true)->getTotalHours());
        static::assertSame(1., $brussels->getDaylightSavingTimeOffset($summer)->getTotalHours());
        static::assertSame(0., $brussels->getDaylightSavingTimeOffset($summer, local: true)->getTotalHours());
    }

    public static function provideRawOffsetData(): iterable
    {
        yield [Timezone::EuropeLondon, 0];
        yield [Timezone::AmericaNewYork, -18000];
        yield [Timezone::AsiaShanghai, 28800];
    }
}
