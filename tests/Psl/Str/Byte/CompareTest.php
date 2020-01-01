<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class CompareTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCompare(int $expected, string $str1, string $str2, ?int $length = null): void
    {
        $diff = Byte\compare($str1, $str2, $length);

        if (0 === $expected) {
            self::assertSame(0, $diff);
        } else if (0 > $expected) {
            self::assertLessThanOrEqual(-1, $diff);
        } else {
            self::assertGreaterThanOrEqual(1, $diff);
        }
    }

    public function provideData(): array
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
