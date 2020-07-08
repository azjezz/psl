<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Collection\IMap;
use Psl\Collection\IVector;
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
    protected string $mapClass = IMap::class;

    /**
     * The Vector class used for values, keys .. etc.
     *
     * @psalm-var class-string<IVector>
     */
    protected string $vectorClass = IVector::class;

    public function testIsEmpty(): void
    {
        $this->assertTrue($this->create([])->isEmpty());
        $this->assertFalse($this->create(['foo' => 'bar'])->isEmpty());
        $this->assertEmpty($this->create(['foo' => null])->isEmpty());
    }

    public function testCount(): void
    {
        $this->assertCount(0, $this->create([]));
        $this->assertCount(1, $this->create(['foo' => 'bar']));
        $this->assertSame(5, $this->create([
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

        $this->assertInstanceOf($this->vectorClass, $values);

        $this->assertCount(3, $values);

        $this->assertSame(1, $values->at(0));
        $this->assertSame(2, $values->at(1));
        $this->assertSame(3, $values->at(2));

        $map = $this->create([]);
        $values = $map->values();
        $this->assertInstanceOf($this->vectorClass, $values);

        $this->assertCount(0, $values);
    }

    public function testKeys(): void
    {
        $map = $this->create([
            'foo' => 1,
            'bar' => 2,
            'baz' => 3,
        ]);
        $keys = $map->keys();

        $this->assertInstanceOf($this->vectorClass, $keys);
        $this->assertCount(3, $keys);
        $this->assertSame('foo', $keys->at(0));
        $this->assertSame('bar', $keys->at(1));
        $this->assertSame('baz', $keys->at(2));

        $map = $this->create([]);
        $keys = $map->keys();

        $this->assertInstanceOf($this->vectorClass, $keys);
        $this->assertCount(0, $keys);
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

        $this->assertInstanceOf($this->mapClass, $filtered);
        $this->assertNotSame($map, $filtered);
        $this->assertContains('bar', $filtered);
        $this->assertContains('baz', $filtered);
        $this->assertNotContains('foo', $filtered);
        $this->assertNotContains('qux', $filtered);
        $this->assertCount(2, $filtered);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filter(fn (string $item) => Str\contains($item, 'hello'));

        $this->assertInstanceOf($this->mapClass, $filtered);
        $this->assertNotContains('bar', $filtered);
        $this->assertNotContains('baz', $filtered);
        $this->assertNotContains('foo', $filtered);
        $this->assertNotContains('qux', $filtered);
        $this->assertCount(0, $filtered);
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

        $this->assertInstanceOf($this->mapClass, $filtered);
        $this->assertNotSame($map, $filtered);
        $this->assertContains('foo', $filtered);
        $this->assertContains('qux', $filtered);
        $this->assertNotContains('bar', $filtered);
        $this->assertNotContains('baz', $filtered);
        $this->assertCount(2, $filtered);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filterWithKey(fn (int $k, string $v) => 4 === $k);

        $this->assertInstanceOf($this->mapClass, $filtered);
        $this->assertNotContains('bar', $filtered);
        $this->assertNotContains('baz', $filtered);
        $this->assertNotContains('foo', $filtered);
        $this->assertNotContains('qux', $filtered);
        $this->assertCount(0, $filtered);
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

        $this->assertInstanceOf($this->mapClass, $mapped);
        $this->assertSame([
            0 => 'FOO',
            1 => 'BAR',
            2 => 'BAZ',
            3 => 'QUX',
        ], $mapped->toArray());
        $this->assertNotSame($map, $mapped);
        $this->assertCount(4, $mapped);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->map(fn (string $item) => $item);

        $this->assertInstanceOf($this->mapClass, $mapped);
        $this->assertNotSame($map, $mapped);
        $this->assertSame($map->toArray(), $mapped->toArray());
        $this->assertCount(4, $mapped);
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

        $this->assertInstanceOf($this->mapClass, $mapped);
        $this->assertSame([
            0 => 'foo ( 0 )',
            1 => 'bar ( 1 )',
            2 => 'baz ( 2 )',
            3 => 'qux ( 3 )',
        ], $mapped->toArray());
        $this->assertNotSame($map, $mapped);
        $this->assertCount(4, $mapped);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->mapWithKey(fn (int $k, string $v) => $k);

        $this->assertInstanceOf($this->mapClass, $mapped);
        $this->assertNotSame($map, $mapped);
        $this->assertSame($map->keys()->toArray(), $mapped->toArray());
        $this->assertCount(4, $mapped);

        $mapped = $map->mapWithKey(fn (int $k, string $v) => $v);

        $this->assertInstanceOf($this->mapClass, $mapped);
        $this->assertNotSame($map, $mapped);
        $this->assertSame($map->toArray(), $mapped->toArray());
        $this->assertCount(4, $mapped);
    }

    public function testFirst(): void
    {
        $map = $this->create([]);
        $this->assertNull($map->first());

        $map = $this->create(['foo' => null]);
        $this->assertNull($map->first());

        $map = $this->create([0 => 'foo']);
        $this->assertSame('foo', $map->first());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertSame('bar', $map->first());
    }

    public function testFirstKey(): void
    {
        $map = $this->create([]);
        $this->assertNull($map->firstKey());

        $map = $this->create(['foo' => null]);
        $this->assertSame('foo', $map->firstKey());

        $map = $this->create([0 => 'foo']);
        $this->assertSame(0, $map->firstKey());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertSame('foo', $map->firstKey());
    }

    public function testLast(): void
    {
        $map = $this->create([]);
        $this->assertNull($map->last());

        $map = $this->create(['foo' => null]);
        $this->assertNull($map->last());

        $map = $this->create([0 => 'foo']);
        $this->assertSame('foo', $map->last());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertSame('qux', $map->last());
    }

    public function testLastKey(): void
    {
        $map = $this->create([]);
        $this->assertNull($map->lastKey());

        $map = $this->create(['foo' => null]);
        $this->assertSame('foo', $map->lastKey());

        $map = $this->create([0 => 'foo']);
        $this->assertSame(0, $map->lastKey());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertSame('baz', $map->lastKey());
    }

    public function testLinearSearch(): void
    {
        $map = $this->create([]);
        $this->assertNull($map->linearSearch('foo'));

        $map = $this->create([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $this->assertSame('foo', $map->linearSearch('bar'));
        $this->assertSame('baz', $map->linearSearch('qux'));
        $this->assertNull($map->linearSearch('foo'));
        $this->assertNull($map->linearSearch('baz'));
    }

    public function testZip(): void
    {
        $map = $this->create([]);
        $zipped = $map->zip([]);
        $this->assertInstanceOf($this->mapClass, $zipped);
        $this->assertCount(0, $zipped);

        $map = $this->create([]);
        $zipped = $map->zip([1, 2]);
        $this->assertInstanceOf($this->mapClass, $zipped);
        $this->assertCount(0, $zipped);

        $map = $this->create([1 => 'foo', 2 => 'bar']);
        $zipped = $map->zip([]);
        $this->assertInstanceOf($this->mapClass, $zipped);
        $this->assertCount(0, $zipped);

        $map = $this->create([1 => 'foo', 2 => 'bar']);
        $zipped = $map->zip(['baz', 'qux']);
        $this->assertInstanceOf($this->mapClass, $zipped);
        $this->assertCount(2, $zipped);
        $this->assertSame(['foo', 'baz'], $zipped->at(1));
        $this->assertSame(['bar', 'qux'], $zipped->at(2));

        $map = $this->create([1 => 'foo', 2 => 'bar', 3 => 'baz', 4 => 'qux']);
        $zipped = $map->zip(['hello', 'world']);
        $this->assertInstanceOf($this->mapClass, $zipped);
        $this->assertCount(2, $zipped);
        $this->assertSame(['foo', 'hello'], $zipped->at(1));
        $this->assertSame(['bar', 'world'], $zipped->at(2));

        $map = $this->create([1 => 'hello', 2 => 'world']);
        $zipped = $map->zip(['foo', 'bar', 'baz', 'qux']);
        $this->assertInstanceOf($this->mapClass, $zipped);
        $this->assertCount(2, $zipped);
        $this->assertSame(['hello', 'foo'], $zipped->at(1));
        $this->assertSame(['world', 'bar'], $zipped->at(2));
    }

    public function testTake(): void
    {
        $map = $this->create([]);
        $rest = $map->take(2);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->take(4);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($map->toArray(), $rest->toArray());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->take(1);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('bar', $rest->at('foo'));
    }

    public function testTakeWhile(): void
    {
        $map = $this->create([]);
        $rest = $map->takeWhile(fn ($v) => false);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create([]);
        $rest = $map->takeWhile(fn ($v) => true);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->takeWhile(fn ($v) => true);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($map->toArray(), $rest->toArray());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->takeWhile(fn ($v) => 'bar' === $v);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('bar', $rest->at('foo'));
    }

    public function testDrop(): void
    {
        $map = $this->create([]);
        $rest = $map->drop(2);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(4);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(1);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('qux', $rest->at('baz'));

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(0);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($map->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $map = $this->create([]);
        $rest = $map->dropWhile(fn ($v) => true);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create([]);
        $rest = $map->dropWhile(fn ($v) => false);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(fn ($v) => true);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(0, $rest);

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(fn ($v) => false);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($map->toArray(), $rest->toArray());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(fn ($v) => 'bar' === $v);
        $this->assertInstanceOf($this->mapClass, $rest);
        $this->assertNotSame($map, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('qux', $rest->at('baz'));
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
        $this->assertInstanceOf($this->mapClass, $slice1);
        $this->assertNotSame($slice1, $map);
        $this->assertCount(1, $slice1);
        $this->assertSame('foo', $slice1->at(0));

        $slice2 = $map->slice(2, 4);
        $this->assertInstanceOf($this->mapClass, $slice1);
        $this->assertNotSame($slice2, $map);
        $this->assertCount(4, $slice2);
        $this->assertSame([
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

        $this->assertSame('hello', $map->at('foo'));
        $this->assertSame('world', $map->at('bar'));

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

        $this->assertTrue($map->contains('foo'));
        $this->assertTrue($map->contains('bar'));
        $this->assertFalse($map->contains('baz'));
    }

    public function testGet(): void
    {
        $map = $this->create([
            'foo' => 'hello',
            'bar' => 'world',
        ]);

        $this->assertSame('hello', $map->get('foo'));
        $this->assertSame('world', $map->get('bar'));
        $this->assertNull($map->get('baz'));
    }

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @psalm-param  iterable<Tk, Tv> $items
     *
     * @psalm-return IMap<Tk, Tv>
     */
    abstract protected function create(iterable $items): IMap;
}
