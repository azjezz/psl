<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ChrTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testChr(string $expected, int $value): void
    {
        static::assertSame($expected, Str\chr($value));
    }

    public function provideData(): array
    {
        return [
            ['م', 1605],
            ['0', 48],
            ['&', 38],
            ['ا', 1575],
            ['A', 65],
        ];
    }
}
