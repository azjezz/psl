<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Range;
use Psl\Str\Byte;
use Psl\Str\Exception;

final class RangeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testRange(string $expected, string $string, Range\RangeInterface $range): void
    {
        static::assertSame($expected, Byte\range($string, $range));
    }

    /**
     * @return list<{0: string, 1: string, 2: Range\RangeInterface}>
     */
    public function provideData(): array
    {
        return [
            ['', '', Range\between(0, 5, upper_inclusive: true)],
            ['Hello,', 'Hello, World!', Range\between(0, 5, upper_inclusive: true)],
            ['Hello', 'Hello, World!', Range\between(0, 5, upper_inclusive: false)],
            ['Hello, World!', 'Hello, World!', Range\from(0)],
            ['World!', 'Hello, World!', Range\between(7, 12, upper_inclusive: true)],
            ['World', 'Hello, World!', Range\between(7, 12, upper_inclusive: false)],
            [
                'destiny',
                'People linked by destiny will always find each other.',
                Range\between(17, 23, upper_inclusive: true),
            ],
            [
                'destiny',
                'People linked by destiny will always find each other.',
                Range\between(17, 24, upper_inclusive: false),
            ],
            ['hel', 'hello world', Range\to(3, inclusive: false)],
            ['', 'lo world', Range\between(3, 3)],
            ['', 'foo', Range\between(3, 3)],
            ['', 'foo', Range\between(3, 12)],
            ['foo', 'foo', Range\full()],
        ];
    }

    public function testRangeThrowsForOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Byte\range('Hello', Range\from(10));
    }

    public function testRangeThrowsForNegativeOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Byte\range('Hello', Range\from(-6));
    }
}
