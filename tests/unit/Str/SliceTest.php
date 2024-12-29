<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;
use Psl\Str\Exception;

final class SliceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSlice(string $expected, string $string, int $offset, null|int $length = null): void
    {
        static::assertSame($expected, Str\slice($string, $offset, $length));
    }

    public function provideData(): array
    {
        return [
            ['', '', 0, 0],
            ['Hello', 'Hello, World!', 0, 5],
            ['Hello, World!', 'Hello, World!', 0],
            ['World', 'Hello, World!', 7, 5],
            ['سيف', 'مرحبا سيف', 6, 3],
            ['اهلا', 'اهلا بكم', 0, 4],
            ['destiny', 'People linked by destiny will always find each other.', 17, 7],
            ['lö ', 'héllö wôrld', 3, 3],
            ['lö wôrld', 'héllö wôrld', 3, null],
            ['', 'héllö wôrld', 3, 0],
            ['', 'fôo', 3, null],
            ['', 'fôo', 3, 12],
            ['wôrld', 'héllö wôrld', -5, null],
            ['wôrld', 'héllö wôrld', -5, 100],
            ['wôr', 'héllö wôrld', -5, 3],
        ];
    }

    public function testSliceThrowsForOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Str\slice('Hello', 10);
    }

    public function testSliceThrowsForNegativeOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Str\slice('hello', -6);
    }
}
