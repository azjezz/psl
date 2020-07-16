<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Gen;
use Psl\Iter;

class DropWhileTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDropWhile(array $expected, iterable $iterable, callable $callable): void
    {
        $result = Iter\drop_while($iterable, $callable);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5], fn (int $_): bool => false];
        yield [[3 => 4, 4 => 5], [1, 2, 3, 4, 5], fn (int $i) => $i <= 3];
        yield [[2 => 3, 3 => 4, 4 => 5], [1, 2, 3, 4, 5], fn (int $i) => $i <= 2];
        yield [[], [1, 2, 3, 4, 5], fn (int $_) => true];

        yield [[1, 2, 3, 4, 5], Iter\range(1, 5), fn (int $_): bool => false];
        yield [[3 => 4, 4 => 5], Iter\range(1, 5), fn (int $i) => $i <= 3];
        yield [[2 => 3, 3 => 4, 4 => 5], Iter\range(1, 5), fn (int $i) => $i <= 2];
        yield [[], Iter\range(1, 5), fn (int $_) => true];

        yield [[1, 2, 3, 4, 5], Gen\range(1, 5), fn (int $_): bool => false];
        yield [[3 => 4, 4 => 5], Gen\range(1, 5), fn (int $i) => $i <= 3];
        yield [[2 => 3, 3 => 4, 4 => 5], Gen\range(1, 5), fn (int $i) => $i <= 2];
        yield [[], Gen\range(1, 5), fn (int $_) => true];
    }
}
