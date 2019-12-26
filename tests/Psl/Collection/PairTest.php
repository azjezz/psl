<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Exception;
use Psl\Iter;
use Psl\Str;

class PairTest extends TestCase
{
    public function testItems(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $items = $pair->items();

        self::assertCount(2, $items);

        self::assertSame('foo', $items[0]);
        self::assertSame('bar', $items[1]);
    }

    public function testIsEmpty(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        self::assertFalse($pair->isEmpty());
    }

    public function testCount(): void
    {
        $pair = new Collection\Pair('foo', 'bar');
        self::assertCount(2, $pair);

        $vector = $pair->filter(fn ($v) => 'foo' === $v);
        self::assertInstanceOf(Collection\ImmVector::class, $vector);
        self::assertCount(1, $vector);

        $vector = $pair->filter(fn ($v) => false);
        self::assertInstanceOf(Collection\ImmVector::class, $vector);
        self::assertCount(0, $vector);
    }

    public function testToArray(): void
    {
        $pair = new Collection\Pair('foo', 'bar');
        $array = $pair->toArray();

        self::assertTrue(Arr\contains($array, 'foo'));
        self::assertTrue(Arr\contains($array, 'bar'));

        $vector = $pair->filter(fn ($v) => false);
        $array = $vector->toArray();

        self::assertEmpty($array);
    }

    public function testAt(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        self::assertSame('foo', $pair->at(0));
        self::assertSame('bar', $pair->at(1));

        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Key (2) is out-of-bound.');

        $pair->at(2);
    }

    public function testContainsKey(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        self::assertTrue($pair->containsKey(0));
        self::assertTrue($pair->containsKey(1));
        self::assertFalse($pair->containsKey(2));
        self::assertFalse($pair->containsKey(3));
    }

    public function testGet(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        self::assertSame('foo', $pair->get(0));
        self::assertSame('bar', $pair->get(1));
        self::assertNull($pair->get(3));
    }

    public function testValues(): void
    {
        $pair = new Collection\Pair('foo', 'bar');
        $values = $pair->values();

        self::assertCount(2, $values);
        self::assertSame('foo', $values->at(0));
        self::assertSame('bar', $values->at(1));
    }

    public function testKeys(): void
    {
        $pair = new Collection\Pair('foo', 'bar');
        $keys = $pair->keys();

        self::assertCount(2, $keys);
        self::assertSame(0, $keys->at(0));
        self::assertSame(1, $keys->at(1));
    }

    public function testMap(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $vector = $pair->map(fn ($value) => Str\uppercase($value));
        self::assertSame('FOO', $vector->at(0));
        self::assertSame('BAR', $vector->at(1));
    }

    public function testMapWithKey(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $vector = $pair->mapWithKey(fn ($key, $value) => Str\format('%s (%d)', $value, $key));
        self::assertSame('foo (0)', $vector->at(0));
        self::assertSame('bar (1)', $vector->at(1));
    }

    public function testFilter(): void
    {
        $pair = new Collection\Pair(1, 2);

        $pair = $pair->filter(fn ($value) => $value >= 2);
        self::assertCount(1, $pair);
        $array = $pair->toArray();

        self::assertFalse(Arr\contains($array, 1));
        self::assertTrue(Arr\contains($array, 2));
    }

    public function testFilterWithKey(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $vector = $pair->filterWithKey(fn ($key, $value) => 0 === $key);
        self::assertCount(1, $vector);

        $array = $vector->toArray();

        self::assertTrue(Arr\contains($array, 'foo'));
        self::assertFalse(Arr\contains($array, 'bar'));
    }

    public function testZip(): void
    {
        $pair = new Collection\Pair(1, 2);

        $vector = $pair->zip(Iter\range(4, 8));

        self::assertCount(2, $vector);

        /** @var Collection\Pair $first */
        $first = $vector->at(0);
        self::assertInstanceOf(Collection\Pair::class, $first);
        self::assertSame(1, $first->first());
        self::assertSame(4, $first->last());

        /** @var Collection\Pair $second */
        $second = $vector->at(1);
        self::assertInstanceOf(Collection\Pair::class, $second);
        self::assertSame(2, $second->first());
        self::assertSame(5, $second->last());
    }

    public function testTake(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $vector = $pair->take(1);

        self::assertCount(1, $vector);
        self::assertSame(['foo'], $vector->toArray());
    }

    public function testTakeWhile(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $vector = $pair->takeWhile(fn ($value) => 'bar' !== $value);

        self::assertCount(1, $vector);
        self::assertSame(['foo'], $vector->toArray());
    }

    public function testDrop(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $vector = $pair->drop(1);

        self::assertCount(1, $vector);
        self::assertSame(['bar'], $vector->toArray());
    }

    public function testDropWhile(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $vector = $pair->dropWhile(fn ($value) => 'bar' !== $value);

        self::assertCount(1, $vector);
        self::assertSame(['bar'], $vector->toArray());
    }

    public function testSlice(): void
    {
        $pair = new Collection\Pair('foo', 'bar');

        $pair = $pair->slice(0, 1);

        self::assertCount(1, $pair);
        self::assertSame(['foo'], $pair->toArray());
    }

    public function testConcat(): void
    {
        $pair = new Collection\Pair(1, 2);
        $vector = $pair->concat(Iter\range(3, 10));

        self::assertCount(10, $vector);
        self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $vector->toArray());
    }

    public function testFirst(): void
    {
        $pair = new Collection\Pair(1, 2);

        self::assertSame(1, $pair->first());
    }

    public function testFirstKey(): void
    {
        $pair = new Collection\Pair(1, 2);

        self::assertSame(0, $pair->firstKey());
    }

    public function testLast(): void
    {
        $pair = new Collection\Pair(1, 2);

        self::assertSame(2, $pair->last());
    }

    public function testLastKey(): void
    {
        $pair = new Collection\Pair(1, 2);

        self::assertSame(1, $pair->lastKey());
    }

    public function testGetIterator(): void
    {
        $pair = new Collection\Pair(1, 2);

        $iterator = $pair->getIterator();
        self::assertInstanceOf(Iter\Iterator::class, $iterator);

        $array = \iterator_to_array($iterator);
        self::assertSame([1, 2], $array);
    }

    public function testLinearSearch(): void
    {
        $pair = new Collection\Pair(1, 2);

        self::assertSame(0, $pair->linearSearch(1));
        self::assertSame(1, $pair->linearSearch(2));

        self::assertNull($pair->linearSearch(5));
    }
}
