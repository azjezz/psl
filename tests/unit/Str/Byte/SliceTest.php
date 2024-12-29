<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;
use Psl\Str\Exception;

final class SliceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSlice(string $expected, string $string, int $offset, null|int $length = null): void
    {
        static::assertSame($expected, Byte\slice($string, $offset, $length));
    }

    public function provideData(): array
    {
        return [
            ['', '', 0, 0],
            ['Hello', 'Hello, World!', 0, 5],
            ['Hello, World!', 'Hello, World!', 0],
            ['World', 'Hello, World!', 7, 5],
            ['destiny', 'People linked by destiny will always find each other.', 17, 7],
        ];
    }

    public function testSliceThrowsForOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Byte\slice('Hello', 10);
    }

    public function testSliceThrowsForNegativeOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Byte\slice('hello', -6);
    }
}
