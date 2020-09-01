<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class ProductTest extends TestCase
{
    public function testProduct(): void
    {
        $result = Iter\product(Iter\range(1, 2), Iter\range(3, 4));
        $entries = Iter\to_array(Iter\enumerate($result));

        self::assertCount(4, $entries);

        self::assertSame($entries[0], [[0, 0], [1, 3]]);
        self::assertSame($entries[1], [[0, 1], [1, 4]]);
        self::assertSame($entries[2], [[1, 0], [2, 3]]);
        self::assertSame($entries[3], [[1, 1], [2, 4]]);
    }

    public function testProductEmpty(): void
    {
        $result = Iter\product(...[]);
        $entries = Iter\to_array(Iter\enumerate($result));

        self::assertCount(1, $entries);

        self::assertSame($entries[0], [[], []]);
    }
}
