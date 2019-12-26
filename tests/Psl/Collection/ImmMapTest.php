<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Exception;
use Psl\Iter;
use Psl\Str;

class ImmMapTest extends TestCase
{
    public function testItems(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        $items = $map->items();

        self::assertSame(2, $items->count());

        self::assertSame('foo', $items->at(0)->first());
        self::assertSame(1, $items->at(0)->last());

        self::assertSame('bar', $items->at(1)->first());
        self::assertSame(2, $items->at(1)->last());
    }

    public function testIsEmpty(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        self::assertFalse($map->isEmpty());

        $map = $map->filter(fn ($v) => false);

        self::assertTrue($map->isEmpty());
    }

    public function testCount(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);
        self::assertCount(2, $map);

        $map = $map->filter(fn ($v) => 1 === $v);
        self::assertCount(1, $map);

        $map = $map->filter(fn ($v) => false);
        self::assertCount(0, $map);
    }

    public function testToArray(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);
        $array = $map->toArray();

        self::assertArrayHasKey('foo', $array);
        self::assertArrayHasKey('bar', $array);

        self::assertSame(1, $array['foo']);
        self::assertSame(2, $array['bar']);

        $map = $map->filter(fn ($v) => false);
        $array = $map->toArray();

        self::assertEmpty($array);
    }

    public function testAt(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        self::assertSame(1, $map->at('foo'));
        self::assertSame(2, $map->at('bar'));

        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Key (baz) is out-of-bound.');

        $map->at('baz');
    }

    public function testContainsKey(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => null]);

        self::assertTrue($map->containsKey('foo'));
        self::assertTrue($map->containsKey('bar'));
        self::assertTrue($map->containsKey('baz'));
        self::assertFalse($map->containsKey('qux'));
    }

    public function testGet(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        self::assertSame(1, $map->get('foo'));
        self::assertSame(2, $map->get('bar'));
        self::assertNull($map->get('baz'));
    }

    public function testContains(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => null]);

        self::assertTrue($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertTrue($map->contains('baz'));
        self::assertFalse($map->contains('qux'));
    }

    public function testValues(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => null]);
        $values = $map->values();

        self::assertCount(3, $values);
        self::assertSame(1, $values->at(0));
        self::assertSame(2, $values->at(1));
        self::assertNull($values->at(2));
    }

    public function testKeys(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => null]);
        $keys = $map->keys();

        self::assertCount(3, $keys);
        self::assertSame('foo', $keys->at(0));
        self::assertSame('bar', $keys->at(1));
        self::assertSame('baz', $keys->at(2));
    }

    public function testMap(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->map(fn ($value) => $value * 2);
        self::assertSame(2, $map->at('foo'));
        self::assertSame(4, $map->at('bar'));
        self::assertSame(6, $map->at('baz'));
    }

    public function testMapWithKey(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->mapWithKey(fn ($key, $value) => Str\format('%s (%d)', $key, $value));
        self::assertSame('foo (1)', $map->at('foo'));
        self::assertSame('bar (2)', $map->at('bar'));
        self::assertSame('baz (3)', $map->at('baz'));
    }

    public function testFilter(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->filter(fn ($value) => $value >= 2);
        self::assertCount(2, $map);

        self::assertFalse($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertTrue($map->contains('baz'));
    }

    public function testFilterWithKey(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->filterWithKey(fn ($key, $value) => $value >= 2 && 'baz' !== $key);
        self::assertCount(1, $map);

        self::assertFalse($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertFalse($map->contains('baz'));
    }

    public function testZip(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        $map = $map->zip(['one', 'two', 'three']);

        /** @var Collection\Pair $foo */
        $foo = $map->at('foo');
        self::assertInstanceOf(Collection\Pair::class, $foo);
        self::assertSame(1, $foo->first());
        self::assertSame('one', $foo->last());

        /** @var Collection\Pair $bar */
        $bar = $map->at('bar');
        self::assertInstanceOf(Collection\Pair::class, $bar);
        self::assertSame(2, $bar->first());
        self::assertSame('two', $bar->last());

        /** @var Collection\Pair $baz */
        $baz = $map->at('baz');
        self::assertInstanceOf(Collection\Pair::class, $baz);
        self::assertSame(3, $baz->first());
        self::assertSame('three', $baz->last());
    }

    public function testTake(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->take(2);

        self::assertCount(2, $map);
        self::assertTrue($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertFalse($map->contains('baz'));
    }

    public function testTakeWhile(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->takeWhile(fn ($v) => 2 !== $v);

        self::assertCount(1, $map);
        self::assertTrue($map->contains('foo'));
        self::assertFalse($map->contains('bar'));
        self::assertFalse($map->contains('baz'));
    }

    public function testDrop(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->drop(1);

        self::assertCount(2, $map);
        self::assertFalse($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertTrue($map->contains('baz'));
    }

    public function testDropWhile(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3]);

        $map = $map->dropWhile(fn ($value) => 3 !== $value);

        self::assertCount(1, $map);
        self::assertFalse($map->contains('foo'));
        self::assertFalse($map->contains('bar'));
        self::assertTrue($map->contains('baz'));
    }

    public function testSlice(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        $map = $map->slice(2, 1);

        self::assertCount(1, $map);
        self::assertFalse($map->contains('foo'));
        self::assertFalse($map->contains('bar'));
        self::assertTrue($map->contains('baz'));
        self::assertFalse($map->contains('qux'));

        $map = $map->slice(0, 0);

        self::assertCount(0, $map);
    }

    public function testConcat(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);
        $vector = $map->concat([5, 6, 7, 8, 9, 10]);

        self::assertCount(10, $vector);
        self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $vector->toArray());
    }

    public function testFirst(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        self::assertSame(1, $map->first());

        $map = new Collection\ImmMap([]);

        self::assertNull($map->first());
    }

    public function testFirstKey(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        self::assertSame('foo', $map->firstKey());

        $map = new Collection\ImmMap([]);

        self::assertNull($map->firstKey());
    }

    public function testLast(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        self::assertSame(4, $map->last());

        $map = new Collection\ImmMap([]);

        self::assertNull($map->last());
    }

    public function testLastKey(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        self::assertSame('qux', $map->lastKey());

        $map = new Collection\ImmMap([]);

        self::assertNull($map->lastKey());
    }

    public function testDifferenceByKey(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        $map = $map->differenceByKey(['foo' => 5, 'baz' => 6]);

        self::assertFalse($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertFalse($map->contains('baz'));
        self::assertTrue($map->contains('qux'));
    }

    public function testMutable(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);
        $mutable = $map->mutable();

        self::assertSame($map->toArray(), $mutable->toArray());
    }

    public function testGetIterator(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        $iterator = $map->getIterator();
        self::assertInstanceOf(Iter\Iterator::class, $iterator);

        $array = \iterator_to_array($iterator);
        self::assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4], $array);
    }
}
