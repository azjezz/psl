<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class ChrTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testChr(string $expected, int $value): void
    {
        static::assertSame($expected, Byte\chr($value));
    }

    public function provideData(): array
    {
        return [
            ['E', 1605],
            ['0', 48],
            ['&', 38],
            ['\'', 1575],
            ['A', 65],
        ];
    }
}
