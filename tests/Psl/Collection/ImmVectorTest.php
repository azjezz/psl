<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Exception;
use Psl\Iter;
use Psl\Str;

class ImmVectorTest extends TestCase
{
    public function testItems(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar']);

        $items = $vector->items();

        self::assertCount(2, $items);

        self::assertSame('foo', $items[0]);
        self::assertSame('bar', $items[1]);
    }

    public function testIsEmpty(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar']);

        self::assertFalse($vector->isEmpty());

        $vector = new Collection\ImmVector([]);

        self::assertTrue($vector->isEmpty());
    }

    public function testCount(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar']);
        self::assertCount(2, $vector);

        $vector = $vector->filter(fn ($v) => 'foo' === $v);
        self::assertCount(1, $vector);

        $vector = $vector->filter(fn ($v) => false);
        self::assertCount(0, $vector);
    }

    public function testToArray(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar']);
        $array = $vector->toArray();

        self::assertTrue(Arr\contains($array, 'foo'));
        self::assertTrue(Arr\contains($array, 'bar'));

        $vector = $vector->filter(fn ($v) => false);
        $array = $vector->toArray();

        self::assertEmpty($array);
    }

    public function testAt(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar']);

        self::assertSame('foo', $vector->at(0));
        self::assertSame('bar', $vector->at(1));

        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Key (2) is out-of-bound.');

        $vector->at(2);
    }

    public function testContainsKey(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', null]);

        self::assertTrue($vector->containsKey(0));
        self::assertTrue($vector->containsKey(1));
        self::assertTrue($vector->containsKey(2));
        self::assertFalse($vector->containsKey(3));
    }

    public function testGet(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar']);

        self::assertSame('foo', $vector->get(0));
        self::assertSame('bar', $vector->get(1));
        self::assertNull($vector->get(3));
    }

    public function testValues(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', null]);
        $values = $vector->values();

        self::assertCount(3, $values);
        self::assertSame('foo', $values->at(0));
        self::assertSame('bar', $values->at(1));
        self::assertNull($values->at(2));
    }

    public function testKeys(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz']);
        $keys = $vector->keys();

        self::assertCount(3, $keys);
        self::assertSame(0, $keys->at(0));
        self::assertSame(1, $keys->at(1));
        self::assertSame(2, $keys->at(2));
    }

    public function testMap(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz']);

        $vector = $vector->map(fn ($value) => Str\uppercase($value));
        self::assertSame('FOO', $vector->at(0));
        self::assertSame('BAR', $vector->at(1));
        self::assertSame('BAZ', $vector->at(2));
    }

    public function testMapWithKey(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz']);

        $vector = $vector->mapWithKey(fn ($key, $value) => Str\format('%s (%d)', $value, $key));
        self::assertSame('foo (0)', $vector->at(0));
        self::assertSame('bar (1)', $vector->at(1));
        self::assertSame('baz (2)', $vector->at(2));
    }

    public function testFilter(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 3));

        $vector = $vector->filter(fn ($value) => $value >= 2);
        self::assertCount(2, $vector);
        $array = $vector->toArray();

        self::assertFalse(Arr\contains($array, 1));
        self::assertTrue(Arr\contains($array, 2));
        self::assertTrue(Arr\contains($array, 3));
    }

    public function testFilterWithKey(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz']);

        $vector = $vector->filterWithKey(fn ($key, $value) => 0 === $key || 'baz' === $value);
        self::assertCount(2, $vector);

        $array = $vector->toArray();

        self::assertTrue(Arr\contains($array, 'foo'));
        self::assertFalse(Arr\contains($array, 'bar'));
        self::assertTrue(Arr\contains($array, 'baz'));
    }

    public function testZip(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 3));

        $vector = $vector->zip(Iter\range(4, 8));

        self::assertCount(3, $vector);

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

        /** @var Collection\Pair $third */
        $third = $vector->at(2);
        self::assertInstanceOf(Collection\Pair::class, $third);
        self::assertSame(3, $third->first());
        self::assertSame(6, $third->last());
    }

    public function testTake(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz', 'qux']);

        $vector = $vector->take(2);

        self::assertCount(2, $vector);
        self::assertSame(['foo', 'bar'], $vector->toArray());
    }

    public function testTakeWhile(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz', 'qux']);

        $vector = $vector->takeWhile(fn ($value) => 'baz' !== $value);

        self::assertCount(2, $vector);
        self::assertSame(['foo', 'bar'], $vector->toArray());
    }

    public function testDrop(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz', 'qux']);

        $vector = $vector->drop(2);

        self::assertCount(2, $vector);
        self::assertSame(['baz', 'qux'], $vector->toArray());
    }

    public function testDropWhile(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz', 'qux']);

        $vector = $vector->dropWhile(fn ($value) => 'baz' !== $value);

        self::assertCount(2, $vector);
        self::assertSame(['baz', 'qux'], $vector->toArray());
    }

    public function testSlice(): void
    {
        $vector = new Collection\ImmVector(['foo', 'bar', 'baz', 'qux']);

        $vector = $vector->slice(2, 1);

        self::assertCount(1, $vector);
        self::assertSame(['baz'], $vector->toArray());
    }

    public function testConcat(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 5));
        $vector = $vector->concat(Iter\range(6, 10));

        self::assertCount(10, $vector);
        self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $vector->toArray());
    }

    public function testFirst(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 5));

        self::assertSame(1, $vector->first());

        $vector = new Collection\ImmVector([]);

        self::assertNull($vector->first());
    }

    public function testFirstKey(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 5));

        self::assertSame(0, $vector->firstKey());

        $vector = new Collection\ImmVector([]);

        self::assertNull($vector->firstKey());
    }

    public function testLast(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 5));

        self::assertSame(5, $vector->last());

        $vector = new Collection\ImmVector([]);

        self::assertNull($vector->last());
    }

    public function testLastKey(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 5));

        self::assertSame(4, $vector->lastKey());

        $vector = new Collection\ImmVector([]);

        self::assertNull($vector->lastKey());
    }

    public function testGetIterator(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 10));

        $iterator = $vector->getIterator();

        $array = \iterator_to_array($iterator);
        self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $array);
    }

    public function testLinearSearch(): void
    {
        $vector = new Collection\ImmVector(Iter\range(1, 4));

        self::assertSame(0, $vector->linearSearch(1));
        self::assertSame(1, $vector->linearSearch(2));
        self::assertSame(2, $vector->linearSearch(3));
        self::assertSame(3, $vector->linearSearch(4));

        self::assertNull($vector->linearSearch(5));
    }
}
