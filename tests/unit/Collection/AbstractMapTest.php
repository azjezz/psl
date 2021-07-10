<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

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
     * @var class-string<IMap>
     */
    protected string $mapClass = MapInterface::class;

    /**
     * The Vector class used for values, keys .. etc.
     *
     * @var class-string<IVector>
     */
    protected string $vectorClass = VectorInterface::class;

    public function testIsEmpty(): void
    {
        static::assertTrue($this->create([])->isEmpty());
        static::assertFalse($this->create(['foo' => 'bar'])->isEmpty());
        static::assertEmpty($this->create(['foo' => null])->isEmpty());
    }

    public function testCount(): void
    {
        static::assertCount(0, $this->create([]));
        static::assertCount(1, $this->create(['foo' => 'bar']));
        static::assertSame(5, $this->create([
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

        static::assertInstanceOf($this->vectorClass, $values);

        static::assertCount(3, $values);

        static::assertSame(1, $values->at(0));
        static::assertSame(2, $values->at(1));
        static::assertSame(3, $values->at(2));

        $map    = $this->create([]);
        $values = $map->values();
        static::assertInstanceOf($this->vectorClass, $values);

        static::assertCount(0, $values);
    }

    public function testJsonSerialize(): void
    {
        $map = $this->create([
            'foo' => 1,
            'bar' => 2,
            'baz' => 3,
        ]);

        $array = $map->jsonSerialize();

        static::assertSame([
            'foo' => 1,
            'bar' => 2,
            'baz' => 3,
        ], $array);
    }

    public function testKeys(): void
    {
        $map  = $this->create([
            'foo' => 1,
            'bar' => 2,
            'baz' => 3,
        ]);
        $keys = $map->keys();

        static::assertInstanceOf($this->vectorClass, $keys);
        static::assertCount(3, $keys);
        static::assertSame('foo', $keys->at(0));
        static::assertSame('bar', $keys->at(1));
        static::assertSame('baz', $keys->at(2));

        $map  = $this->create([]);
        $keys = $map->keys();

        static::assertInstanceOf($this->vectorClass, $keys);
        static::assertCount(0, $keys);
    }

    public function testFilter(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filter(static fn (string $item) => Str\contains($item, 'b'));

        static::assertInstanceOf($this->mapClass, $filtered);
        static::assertNotSame($map, $filtered);
        static::assertContains('bar', $filtered);
        static::assertContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(2, $filtered);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filter(static fn (string $item) => Str\contains($item, 'hello'));

        static::assertInstanceOf($this->mapClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testFilterWithKey(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filterWithKey(static fn (int $k, string $v) => 'foo' === $v || 3 === $k);

        static::assertInstanceOf($this->mapClass, $filtered);
        static::assertNotSame($map, $filtered);
        static::assertContains('foo', $filtered);
        static::assertContains('qux', $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertCount(2, $filtered);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $filtered = $map->filterWithKey(static fn (int $k, string $v) => 4 === $k);

        static::assertInstanceOf($this->mapClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testMap(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->map(static fn (string $item) => Str\uppercase($item));

        static::assertInstanceOf($this->mapClass, $mapped);
        static::assertSame([
            0 => 'FOO',
            1 => 'BAR',
            2 => 'BAZ',
            3 => 'QUX',
        ], $mapped->toArray());
        static::assertNotSame($map, $mapped);
        static::assertCount(4, $mapped);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->map(static fn (string $item) => $item);

        static::assertInstanceOf($this->mapClass, $mapped);
        static::assertNotSame($map, $mapped);
        static::assertSame($map->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }

    public function testMapWithKey(): void
    {
        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->mapWithKey(static fn (int $k, string $v) => Str\format('%s ( %d )', $v, $k));

        static::assertInstanceOf($this->mapClass, $mapped);
        static::assertSame([
            0 => 'foo ( 0 )',
            1 => 'bar ( 1 )',
            2 => 'baz ( 2 )',
            3 => 'qux ( 3 )',
        ], $mapped->toArray());
        static::assertNotSame($map, $mapped);
        static::assertCount(4, $mapped);

        $map = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $map->mapWithKey(static fn (int $k, string $v) => $k);

        static::assertInstanceOf($this->mapClass, $mapped);
        static::assertNotSame($map, $mapped);
        static::assertSame($map->keys()->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);

        $mapped = $map->mapWithKey(static fn (int $k, string $v) => $v);

        static::assertInstanceOf($this->mapClass, $mapped);
        static::assertNotSame($map, $mapped);
        static::assertSame($map->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }

    public function testFirst(): void
    {
        $map = $this->create([]);
        static::assertNull($map->first());

        $map = $this->create(['foo' => null]);
        static::assertNull($map->first());

        $map = $this->create([0 => 'foo']);
        static::assertSame('foo', $map->first());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        static::assertSame('bar', $map->first());
    }

    public function testFirstKey(): void
    {
        $map = $this->create([]);
        static::assertNull($map->firstKey());

        $map = $this->create(['foo' => null]);
        static::assertSame('foo', $map->firstKey());

        $map = $this->create([0 => 'foo']);
        static::assertSame(0, $map->firstKey());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        static::assertSame('foo', $map->firstKey());
    }

    public function testLast(): void
    {
        $map = $this->create([]);
        static::assertNull($map->last());

        $map = $this->create(['foo' => null]);
        static::assertNull($map->last());

        $map = $this->create([0 => 'foo']);
        static::assertSame('foo', $map->last());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        static::assertSame('qux', $map->last());
    }

    public function testLastKey(): void
    {
        $map = $this->create([]);
        static::assertNull($map->lastKey());

        $map = $this->create(['foo' => null]);
        static::assertSame('foo', $map->lastKey());

        $map = $this->create([0 => 'foo']);
        static::assertSame(0, $map->lastKey());

        $map = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        static::assertSame('baz', $map->lastKey());
    }

    public function testLinearSearch(): void
    {
        $map = $this->create([]);
        static::assertNull($map->linearSearch('foo'));

        $map = $this->create([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        static::assertSame('foo', $map->linearSearch('bar'));
        static::assertSame('baz', $map->linearSearch('qux'));
        static::assertNull($map->linearSearch('foo'));
        static::assertNull($map->linearSearch('baz'));
    }

    public function testZip(): void
    {
        $map    = $this->create([]);
        $zipped = $map->zip([]);
        static::assertInstanceOf($this->mapClass, $zipped);
        static::assertCount(0, $zipped);

        $map    = $this->create([]);
        $zipped = $map->zip([1, 2]);
        static::assertInstanceOf($this->mapClass, $zipped);
        static::assertCount(0, $zipped);

        $map    = $this->create([1 => 'foo', 2 => 'bar']);
        $zipped = $map->zip([]);
        static::assertInstanceOf($this->mapClass, $zipped);
        static::assertCount(0, $zipped);

        $map    = $this->create([1 => 'foo', 2 => 'bar']);
        $zipped = $map->zip(['baz', 'qux']);
        static::assertInstanceOf($this->mapClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['foo', 'baz'], $zipped->at(1));
        static::assertSame(['bar', 'qux'], $zipped->at(2));

        $map    = $this->create([1 => 'foo', 2 => 'bar', 3 => 'baz', 4 => 'qux']);
        $zipped = $map->zip(['hello', 'world']);
        static::assertInstanceOf($this->mapClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['foo', 'hello'], $zipped->at(1));
        static::assertSame(['bar', 'world'], $zipped->at(2));

        $map    = $this->create([1 => 'hello', 2 => 'world']);
        $zipped = $map->zip(['foo', 'bar', 'baz', 'qux']);
        static::assertInstanceOf($this->mapClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['hello', 'foo'], $zipped->at(1));
        static::assertSame(['world', 'bar'], $zipped->at(2));
    }

    public function testTake(): void
    {
        $map  = $this->create([]);
        $rest = $map->take(2);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->take(4);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(2, $rest);
        static::assertSame($map->toArray(), $rest->toArray());

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->take(1);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at('foo'));
    }

    public function testTakeWhile(): void
    {
        $map  = $this->create([]);
        $rest = $map->takeWhile(static fn ($v) => false);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create([]);
        $rest = $map->takeWhile(static fn ($v) => true);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->takeWhile(static fn ($v) => true);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(2, $rest);
        static::assertSame($map->toArray(), $rest->toArray());

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->takeWhile(static fn ($v) => 'bar' === $v);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at('foo'));
    }

    public function testDrop(): void
    {
        $map  = $this->create([]);
        $rest = $map->drop(2);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(4);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(1);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at('baz'));

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->drop(0);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(2, $rest);
        static::assertSame($map->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $map  = $this->create([]);
        $rest = $map->dropWhile(static fn ($v) => true);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create([]);
        $rest = $map->dropWhile(static fn ($v) => false);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(static fn ($v) => true);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(0, $rest);

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(static fn ($v) => false);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(2, $rest);
        static::assertSame($map->toArray(), $rest->toArray());

        $map  = $this->create(['foo' => 'bar', 'baz' => 'qux']);
        $rest = $map->dropWhile(static fn ($v) => 'bar' === $v);
        static::assertInstanceOf($this->mapClass, $rest);
        static::assertNotSame($map, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at('baz'));
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
        static::assertInstanceOf($this->mapClass, $slice1);
        static::assertNotSame($slice1, $map);
        static::assertCount(1, $slice1);
        static::assertSame('foo', $slice1->at(0));

        $slice2 = $map->slice(2, 4);
        static::assertInstanceOf($this->mapClass, $slice1);
        static::assertNotSame($slice2, $map);
        static::assertCount(4, $slice2);
        static::assertSame([
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

        static::assertSame('hello', $map->at('foo'));
        static::assertSame('world', $map->at('bar'));

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

        static::assertTrue($map->contains('foo'));
        static::assertTrue($map->contains('bar'));
        static::assertFalse($map->contains('baz'));
    }

    public function testGet(): void
    {
        $map = $this->create([
            'foo' => 'hello',
            'bar' => 'world',
        ]);

        static::assertSame('hello', $map->get('foo'));
        static::assertSame('world', $map->get('bar'));
        static::assertNull($map->get('baz'));
    }

    public function testChunk(): void
    {
        $map = $this->create([
            'foo' => 'hello',
            'bar' => 'world',
            'baz' => '!'
        ]);

        $chunks = $map->chunk(2);

        static::assertCount(2, $chunks);
        static::assertSame(['foo' => 'hello', 'bar' => 'world'], $chunks->at(0)->toArray());
        static::assertSame(['baz' => '!'], $chunks->at(1)->toArray());

        $chunks = $map->chunk(1);

        static::assertCount(3, $chunks);
        static::assertSame(['foo' => 'hello'], $chunks->at(0)->toArray());
        static::assertSame(['bar' => 'world'], $chunks->at(1)->toArray());
        static::assertSame(['baz' => '!'], $chunks->at(2)->toArray());
    }

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @param iterable<Tk, Tv> $items
     *
     * @return IMap<Tk, Tv>
     */
    abstract protected function create(iterable $items): MapInterface;
}
