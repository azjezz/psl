<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

class ValuesTest extends TestCase
{
    /**
     * @dataProvider provideTestValues
     */
    public function testValues(array $expected, iterable $iterable): void
    {
        $result = Iter\values($iterable);
        $result = Iter\to_array($result);

        static::assertSame($expected, $result);
    }

    public function provideTestValues(): iterable
    {
        yield [[], []];
        yield [[null], [null]];
        yield [[1, 2], [1, 2]];
        yield [[1, 2, 3, 4, 5], Iter\range(1, 5)];
        yield [['hello', 'world'], new Collection\Map(['foo' => 'hello', 'bar' => 'world'])];
        yield [['foo', 'bar'], new Collection\Vector(['foo', 'bar'])];
    }
}
