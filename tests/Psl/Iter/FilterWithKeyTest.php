<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

class FilterWithKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterWithKey(array $expected, iterable $iterable, ?callable $predicate = null): void
    {
        $result = Iter\filter_with_key($iterable, $predicate);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [['a', 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], fn (int $_k, string $_v) => false];
        yield  [['a', 'b'], ['a', 'b'], fn (int $_k, string $_v): bool => true];
        yield  [['a'], ['a', 'b'], fn (int $k, string $v): bool => 'b' !== $v];
        yield  [[], ['a', 'b'], fn (int $k, string $v): bool => 'b' !== $v && 0 !== $k];
        yield  [['a'], ['a', 'b'], fn (int $k, string $v): bool => 'b' !== $v && 1 !== $k];
        yield  [[], ['a', 'b'], fn (int $k, string $v): bool => 'a' !== $v && 1 !== $k];
        yield  [[1 => 'b'], ['a', 'b'], fn (int $k, string $v): bool => 'a' !== $v && 0 !== $k];

        yield  [[], new \SplFixedArray(0)];
        yield  [['a', 'b'], new Collection\Vector(['a', 'b'])];
        yield  [[], new Collection\Map(['a', 'b']), fn (int $_k, string $_v) => false];
        yield  [['a', 'b'], new Iter\Iterator(['a', 'b']), fn (int $_k, string $_v): bool => true];
        yield  [['a'], new Collection\MutableMap(['a', 'b']), fn (int $k, string $v): bool => 'b' !== $v];
        yield  [[], new Collection\MutableVector(['a', 'b']), fn (int $k, string $v): bool => 'b' !== $v && 0 !== $k];
        yield  [['a'], new Iter\Iterator(['a', 'b']), fn (int $k, string $v): bool => 'b' !== $v && 1 !== $k];
        yield  [[], new \ArrayIterator(['a', 'b']), fn (int $k, string $v): bool => 'a' !== $v && 1 !== $k];
        yield  [[1 => 'b'], new \ArrayObject(['a', 'b']), fn (int $k, string $v): bool => 'a' !== $v && 0 !== $k];

        $doublyLinkedList = new \SplDoublyLinkedList();
        $doublyLinkedList->add(0, 'a');
        $doublyLinkedList->add(1, 'b');
        yield  [['a', 'b'], $doublyLinkedList];
    }
}
