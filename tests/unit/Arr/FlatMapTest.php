<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Str;

final class FlatMapTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFlatMap(array $expected, array $array, callable $function): void
    {
        $result = Arr\flat_map($array, $function);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], static fn (int $v): array => [$v]];
        yield [[1, 2, 2, 4, 3, 6], [1, 2, 3], static fn (int $v): array => [$v, $v * 2]];
        yield [[], [1, 2], static fn (int $k): array => []];
        yield [[[1], [2]], [1, 2], static fn (int $v): array => [[$v]]];
        yield [[], [], static fn (int $k): array => []];
        yield [
            ['The', 'quick', 'brown', 'fox', 'jumps', 'over', 'the', 'lazy', 'dog', ''],
            ['The quick brown', 'fox', 'jumps over', 'the lazy dog', ''],
            static fn (string $v): array => Str\Split($v, ' ')
        ];
    }
}
