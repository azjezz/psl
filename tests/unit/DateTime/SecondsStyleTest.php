<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\SecondsStyle;
use Psl\DateTime\Timestamp;

final class SecondsStyleTest extends TestCase
{
    use DateTimeTestTrait;

    /**
     * @dataProvider provideFromTimestampData
     */
    public function testFromTimestamp(SecondsStyle $expectedSecondsStyle, Timestamp $timestamp): void
    {
        static::assertSame($expectedSecondsStyle, SecondsStyle::fromTimestamp($timestamp));
    }

    /**
     * @return iterable<array{SecondsStyle, Timestamp}>
     */
    public static function provideFromTimestampData(): iterable
    {
        yield [SecondsStyle::Seconds, Timestamp::fromParts(0)];
        yield [SecondsStyle::Milliseconds, Timestamp::fromParts(0, 1000000)];
        yield [SecondsStyle::Microseconds, Timestamp::fromParts(0, 1000)];
        yield [SecondsStyle::Nanoseconds, Timestamp::fromParts(0, 1)];
    }
}
