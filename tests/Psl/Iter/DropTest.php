<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Gen;
use Psl\Iter;

class DropTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDrop(array $expected, iterable $iterable, int $n): void
    {
        $result = Iter\drop($iterable, $n);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5], 0];
        yield [[3 => 4, 4 => 5], [1, 2, 3, 4, 5], 3];
        yield [[2 => 3, 3 => 4, 4 => 5], [1, 2, 3, 4, 5], 2];
        yield [[], [1, 2, 3, 4, 5], 5];

        yield [[1, 2, 3, 4, 5], Iter\range(1, 5), 0];
        yield [[3 => 4, 4 => 5], Iter\range(1, 5), 3];
        yield [[2 => 3, 3 => 4, 4 => 5], Iter\range(1, 5), 2];
        yield [[], Iter\range(1, 5), 5];

        yield [[1, 2, 3, 4, 5], Gen\range(1, 5), 0];
        yield [[3 => 4, 4 => 5], Gen\range(1, 5), 3];
        yield [[2 => 3, 3 => 4, 4 => 5], Gen\range(1, 5), 2];
        yield [[], Gen\range(1, 5), 5];
    }
}
