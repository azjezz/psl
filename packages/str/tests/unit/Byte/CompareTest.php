<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class CompareTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testCompare(int $expected, string $str1, string $str2, null|int $length = null): void
    {
        $diff = Byte\compare($str1, $str2, $length);

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

    public static function provideData(): array
    {
        return [
            [-1, 'Hello', 'hello'],
            [1, 'hello', 'Hello'],
            [0, 'Hello', 'Hello'],
            [-1, 'Hello', 'helloo'],
            [-1, 'Helloo', 'hello'],
            [-1, 'Helloo', 'hello', 2],
            [-1, 'Helloo', 'hello', 5],
            [-1, 'Helloo', 'hello', 6],
        ];
    }
}
