<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ToIntTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testToInt(null|int $expected, string $string): void
    {
        static::assertSame($expected, Str\to_int($string));
    }

    public function provideData(): array
    {
        return [
            [null, 'hello'],
            [1, '1'],
            [525, '525'],
            [0, '0'],
            [null, '0e'],
            [null, '12e1'],
        ];
    }
}
