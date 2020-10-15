<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Str\Byte;

final class SliceTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testSlice(string $expected, string $string, int $offset, ?int $length = null): void
    {
        static::assertSame($expected, Byte\slice($string, $offset, $length));
    }

    public function provideData(): array
    {
        return [
            ['', '', 0, 0, ],
            ['Hello', 'Hello, World!', 0, 5],
            ['Hello, World!', 'Hello, World!', 0],
            ['World', 'Hello, World!', 7, 5],
            ['destiny', 'People linked by destiny will always find each other.', 17, 7],
        ];
    }

    public function testSliceThrowsForNegativeLength(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);

        Byte\slice('Hello', 0, -1);
    }

    public function testSliceThrowsForOutOfBoundOffset(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);

        Byte\slice('Hello', 10);
    }

    public function testSliceThrowsForNegativeOutOfBoundOffset(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);

        Byte\slice('hello', -6);
    }
}
