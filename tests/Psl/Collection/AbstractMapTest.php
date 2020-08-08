<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Collection\MapInterface;
use Psl\Collection\VectorInterface;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

/**
 * @covers \Psl\Collection\AbstractMap
 * @covers \Psl\Collection\AbstractAccessibleCollection
 */
abstract class AbstractMapTest extends TestCase
{
    /**
     * The Map class being currently tested.
     *
     * @psalm-var class-string<IMap>
     */
    protected string $mapClass = MapInterface::class;

    /**
     * The Vector class used for values, keys .. etc.
     *
     * @psalm-var class-string<IVector>
     */
    protected string $vectorClass = VectorInterface::class;

    public function testIsEmpty(): void
    {
        self::assertTrue($this->create([])->isEmpty());
        self::assertFalse($this->create(['foo' => 'bar'])->isEmpty());
        self::assertEmpty($this->create(['foo' => null])->isEmpty());
    }

    public function testCount(): void
    {
        self::assertCount(0, $this->create([]));
        self::assertCount(1, $this->create(['foo' => 'bar']));
        self::assertSame(5, $this->create([
            1 => 'foo',
            2 => 'bar',
            4 => 'baz',
            8 => 'qux',
            16 => 'hax' // ??
        ])->count());
    }

    public function testValues(): void
    {
        $map = $this->create([
            'foo' => 1,
            'bar' => 2,
            'baz' => 3,
        ]);

        $values = $map->values();

        self::assertInstanceOf($this->vectorClass, $values);

        self::assertCount(3, $values);

        self::assertSame(1, $values->at(0));
        self::assertSame(2, $values->at(1));
        self::assertSame(3, $values->at(2));

        $map = $this->create([]);
        $values = $map->values();
        self::assertInstanceOf($this->vectorClass, $values);

        self::assertCount(0, $values);
    }

    public function testKeys(): void
    {
        $map = $this->create([
            'foo' => 1,
            'bar' => 2,
            'baz' => 3,
        ]);
        $keys = $map->keys();

        self::assertInstanceOf($this->vectorClass, $keys);
        self::assertCount(3, $keys);
        self::assertSame('foo', $keys->at(0));
        self::assertSame('bar', $keys->at(1));
        self::assertSame('baz', $keys->at(2));

        $map = $this->create([]);
        $keys = $map->keys();

        self::assertInstanceOf($this->vectorClass, $keys);
        self::assertCount(0, $keys);
    }

    public function testFilter(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filter(fn (string $item) => Str\contains($item, 'b'));

        self::assertInstanceOf($this->mapClass, $filtered);
        self::assertNotSame($map, $filtered);
        self::assertContains('bar', $filtered);
        self::assertContains('baz', $filtered);
        self::assertNotContains('foo', $filtered);
        self::assertNotContains('qux', $filtered);
        self::assertCount(2, $filtered);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filter(fn (string $item) => Str\contains($item, 'hello'));

        self::assertInstanceOf($this->mapClass, $filtered);
        self::assertNotContains('bar', $filtered);
        self::assertNotContains('baz', $filtered);
        self::assertNotContains('foo', $filtered);
        self::assertNotContains('qux', $filtered);
        self::assertCount(0, $filtered);
    }

    public function testFilterWithKey(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filterWithKey(fn (int $k, string $v) => 'foo' === $v || 3 === $k);

        self::assertInstanceOf($this->mapClass, $filtered);
        self::assertNotSame($map, $filtered);
        self::assertContains('foo', $filtered);
        self::assertContains('qux', $filtered);
        self::assertNotContains('bar', $filtered);
        self::assertNotContains('baz', $filtered);
        self::assertCount(2, $filtered);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filterWithKey(fn (int $k, string $v) => 4 === $k);

        self::assertInstanceOf($this->mapClass, $filtered);
        self::assertNotContains('bar', $filtered);
        self::assertNotContains('baz', $filtered);
        self::assertNotContains('foo', $filtered);
        self::assertNotContains('qux', $filtered);
        self::assertCount(0, $filtered);
    }

    public function testMap(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->map(fn (string $item) => Str\uppercase($item));

        self::assertInstanceOf($this->mapClass, $mapped);
        self::assertSame([
            0 => 'FOO',
            1 => 'BAR',
            2 => 'BAZ',
            3 => 'QUX',
        ], $mapped->toArray());
        self::assertNotSame($map, $mapped);
        self::assertCount(4, $mapped);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->map(fn (string $item) => $item);

        self::assertInstanceOf($this->mapClass, $mapped);
        self::assertNotSame($map, $mapped);
        self::assertSame($map->toArray(), $mapped->toArray());
        self::assertCount(4, $mapped);
    }

    public function testMapWithKey(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->mapWithKey(fn (int $k, string $v) => Str\format('%s ( %d )', $v, $k));

        self::assertInstanceOf($this->mapClass, $mapped);
        self::assertSame([
            0 => 'foo ( 0 )',
            1 => 'bar ( 1 )',
            2 => 'baz ( 2 )',
            3 => 'qux ( 3 )',
        ], $mapped->toArray());
        self::assertNotSame($map, $mapped);
        self::assertCount(4, $mapped);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->mapWithKey(fn (int $k, string $v) => $k);

        self::assertInstanceOf($this->mapClass, $mapped);
        self::assertNotSame($map, $mapped);
        self::assertSame($map->keys()->toArray(), $mapped->toArray());
        self::assertCount(4, $mapped);

        $mapped = $map->mapWithKey(fn (int $k, string $v) => $v);

        self::assertInstanceOf($this->mapClass, $mapped);
        self::assertNotSame($map, $mapped);
        self::assertSame($map->toArray(), $mapped->toArray());
        self::assertCount(4, $mapped);
    }

    public function testFirst(): void
    {
        $map = $this->create([]);
        self::assertNull($map->first());

        $map = $this->create(['foo' => null]);
        self::assertNull($map->first());

        $map = $this->create([0 => 'foo']);
        self::assertSame('foo', $map->first());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        self::assertSame('bar', $map->first());
    }

    public function testFirstKey(): void
    {
        $map = $this->create([]);
        self::assertNull($map->firstKey());

        $map = $this->create(['foo' => null]);
        self::assertSame('foo', $map->firstKey());

        $map = $this->create([0 => 'foo']);
        self::assertSame(0, $map->firstKey());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        self::assertSame('foo', $map->firstKey());
    }

    public function testLast(): void
    {
        $map = $this->create([]);
        self::assertNull($map->last());

        $map = $this->create(['foo' => null]);
        self::assertNull($map->last());

        $map = $this->create([0 => 'foo']);
        self::assertSame('foo', $map->last());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        self::assertSame('qux', $map->last());
    }

    public function testLastKey(): void
    {
        $map = $this->create([]);
        self::assertNull($map->lastKey());

        $map = $this->create(['foo' => null]);
        self::assertSame('foo', $map->lastKey());

        $map = $this->create([0 => 'foo']);
        self::assertSame(0, $map->lastKey());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        self::assertSame('baz', $map->lastKey());
    }

    public function testLinearSearch(): void
    {
        $map = $this->create([]);
        self::assertNull($map->linearSearch('foo'));

        $map = $this->create([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        self::assertSame('foo', $map->linearSearch('bar'));
        self::assertSame('baz', $map->linearSearch('qux'));
        self::assertNull($map->linearSearch('foo'));
        self::assertNull($map->linearSearch('baz'));
    }

    public function testZip(): void
    {
        $map = $this->create([]);
        $zipped = $map->zip([]);
        self::assertInstanceOf($this->mapClass, $zipped);
        self::assertCount(0, $zipped);

        $map = $this->create([]);
        $zipped = $map->zip([1, 2]);
        self::assertInstanceOf($this->mapClass, $zipped);
        self::assertCount(0, $zipped);

        $map = $this->create([1 => 'foo', 2 => 'bar']);
        $zipped = $map->zip([]);
        self::assertInstanceOf($this->mapClass, $zipped);
        self::assertCount(0, $zipped);

        $map = $this->create([1 => 'foo', 2 => 'bar']);
        $zipped = $map->zip(['baz', 'qux']);
        self::assertInstanceOf($this->mapClass, $zipped);
        self::assertCount(2, $zipped);
        self::assertSame(['foo', 'baz'], $zipped->at(1));
        self::assertSame(['bar', 'qux'], $zipped->at(2));

        $map = $this->create([1 => 'foo', 2 => 'bar', 3 => 'baz', 4 => 'qux']);
        $zipped = $map->zip(['hello', 'world']);
        self::assertInstanceOf($this->mapClass, $zipped);
        self::assertCount(2, $zipped);
        self::assertSame(['foo', 'hello'], $zipped->at(1));
        self::assertSame(['bar', 'world'], $zipped->at(2));

        $map = $this->create([1 => 'hello', 2 => 'world']);
        $zipped = $map->zip(['foo', 'bar', 'baz', 'qux']);
        self::assertInstanceOf($this->mapClass, $zipped);
        self::assertCount(2, $zipped);
        self::assertSame(['hello', 'foo'], $zipped->at(1));
        self::assertSame(['world', 'bar'], $zipped->at(2));
    }

    public function testTake(): void
    {
        $map = $this->create([]);
        $rest = $map->take(2);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->take(4);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(2, $rest);
        self::assertSame($map->toArray(), $rest->toArray());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->take(1);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(1, $rest);
        self::assertSame('bar', $rest->at('foo'));
    }

    public function testTakeWhile(): void
    {
        $map = $this->create([]);
        $rest = $map->takeWhile(fn ($v) => false);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create([]);
        $rest = $map->takeWhile(fn ($v) => true);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->takeWhile(fn ($v) => true);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(2, $rest);
        self::assertSame($map->toArray(), $rest->toArray());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->takeWhile(fn ($v) => 'bar' === $v);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(1, $rest);
        self::assertSame('bar', $rest->at('foo'));
    }

    public function testDrop(): void
    {
        $map = $this->create([]);
        $rest = $map->drop(2);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(4);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(1);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(1, $rest);
        self::assertSame('qux', $rest->at('baz'));

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(0);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(2, $rest);
        self::assertSame($map->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $map = $this->create([]);
        $rest = $map->dropWhile(fn ($v) => true);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create([]);
        $rest = $map->dropWhile(fn ($v) => false);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(fn ($v) => true);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(fn ($v) => false);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(2, $rest);
        self::assertSame($map->toArray(), $rest->toArray());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(fn ($v) => 'bar' === $v);
        self::assertInstanceOf($this->mapClass, $rest);
        self::assertNotSame($map, $rest);
        self::assertCount(1, $rest);
        self::assertSame('qux', $rest->at('baz'));
    }

    public function testSlice(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'foo',
            2 => 'bar',
            3 => 'bar',
            4 => 'baz',
            5 => 'baz',
            6 => 'qux',
            7 => 'qux',
        ]);

        $slice1 = $map->slice(0, 1);
        self::assertInstanceOf($this->mapClass, $slice1);
        self::assertNotSame($slice1, $map);
        self::assertCount(1, $slice1);
        self::assertSame('foo', $slice1->at(0));

        $slice2 = $map->slice(2, 4);
        self::assertInstanceOf($this->mapClass, $slice1);
        self::assertNotSame($slice2, $map);
        self::assertCount(4, $slice2);
        self::assertSame([
            2 => 'bar',
            3 => 'bar',
            4 => 'baz',
            5 => 'baz',
        ], $slice2->toArray());
    }

    public function testAt(): void
    {
        $map = $this->create([
            'foo' => 'hello',
            'bar' => 'world',
        ]);

        self::assertSame('hello', $map->at('foo'));
        self::assertSame('world', $map->at('bar'));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (baz) is out-of-bounds.');

        $map->at('baz');
    }

    public function testContains(): void
    {
        $map = $this->create([
            'foo' => 'hello',
            'bar' => 'world',
        ]);

        self::assertTrue($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertFalse($map->contains('baz'));
    }

    public function testGet(): void
    {
        $map = $this->create([
            'foo' => 'hello',
            'bar' => 'world',
        ]);

        self::assertSame('hello', $map->get('foo'));
        self::assertSame('world', $map->get('bar'));
        self::assertNull($map->get('baz'));
    }

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @psalm-param  iterable<Tk, Tv> $items
     *
     * @psalm-return IMap<Tk, Tv>
     */
    abstract protected function create(iterable $items): MapInterface;
}
