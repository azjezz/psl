<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
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

    public static function provideRawOffsetData(): iterable
    {
        yield [Timezone::EuropeLondon, 0];
        yield [Timezone::AmericaNewYork, -18000];
        yield [Timezone::AsiaShanghai, 28800];
    }
}
