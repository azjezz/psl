<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class CompareCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCompareCi(int $expected, string $str1, string $str2, null|int $length = null): void
    {
        $diff = Byte\compare_ci($str1, $str2, $length);

        if (0 === $expected) {
            static::assertSame(0, $diff);

            return;
        }

        if (0 > $expected) {
            static::assertLessThanOrEqual(-1, $diff);

            return;
        }

        static::assertGreaterThanOrEqual(1, $diff);
    }

    public function provideData(): array
    {
        return [
            [0, 'Hello', 'hello'],
            [0, 'hello', 'Hello'],
            [0, 'Hello', 'Hello'],
            [-1, 'Hello', 'helloo'],
            [1, 'Helloo', 'hello'],
            [0, 'Helloo', 'hello', 2],
            [0, 'Helloo', 'hello', 5],
            [1, 'Helloo', 'hello', 6],
        ];
    }
}
