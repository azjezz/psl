<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class TakeWhileTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTakeWhile(array $expected, iterable $iterable, callable $callable): void
    {
        $result = Iter\take_while($iterable, $callable);

        static::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[], [1, 2, 3, 4, 5], static fn (int $_): bool => false];
        yield [[1, 2, 3], [1, 2, 3, 4, 5], static fn (int $i) => $i <= 3];
        yield [[1, 2], [1, 2, 3, 4, 5], static fn (int $i) => $i <= 2];
        yield [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5], static fn (int $_) => true];

        yield [[], Iter\range(1, 5), static fn (int $_): bool => false];
        yield [[1, 2, 3], Iter\range(1, 5), static fn (int $i) => $i <= 3];
        yield [[1, 2], Iter\range(1, 5), static fn (int $i) => $i <= 2];
        yield [[1, 2, 3, 4, 5], Iter\range(1, 5), static fn (int $_) => true];

        yield [[], Iter\range(1, 5), static fn (int $_): bool => false];
        yield [[1, 2 , 3], Iter\range(1, 5), static fn (int $i) => $i <= 3];
        yield [[1, 2], Iter\range(1, 5), static fn (int $i) => $i <= 2];
        yield [[1, 2, 3, 4, 5], Iter\range(1, 5), static fn (int $_) => true];
    }
}
