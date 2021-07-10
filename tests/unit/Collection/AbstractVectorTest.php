<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Collection\VectorInterface;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

/**
 * @covers \Psl\Collection\AbstractVector
 * @covers \Psl\Collection\AbstractAccessibleCollection
 */
abstract class AbstractVectorTest extends TestCase
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @var class-string<IVector>
     */
    protected string $vectorClass = VectorInterface::class;

    public function testIsEmpty(): void
    {
        static::assertTrue($this->create([])->isEmpty());
        static::assertFalse($this->create(['foo', 'bar'])->isEmpty());
        static::assertEmpty($this->create([null])->isEmpty());
    }

    public function testCount(): void
    {
        static::assertCount(0, $this->create([]));
        static::assertCount(2, $this->create(['foo', 'bar']));
        static::assertSame(5, $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
            'hax' // ??
        ])->count());
    }

    public function testValues(): void
    {
        $vector = $this->create([1, 2, 3]);

        $values = $vector->values();

        static::assertInstanceOf($this->vectorClass, $values);

        static::assertCount(3, $values);

        static::assertSame(1, $values->at(0));
        static::assertSame(2, $values->at(1));
        static::assertSame(3, $values->at(2));

        $vector = $this->create([]);
        $values = $vector->values();
        static::assertInstanceOf($this->vectorClass, $values);

        static::assertCount(0, $values);
    }

    public function testJsonSerialize(): void
    {
        $vector = $this->create(['foo', 'bar', 'baz']);

        $array = $vector->jsonSerialize();

        static::assertSame(['foo', 'bar', 'baz'], $array);
    }

    public function testKeys(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
        ]);
        $keys   = $vector->keys();

        static::assertInstanceOf($this->vectorClass, $keys);
        static::assertCount(3, $keys);
        static::assertSame(0, $keys->at(0));
        static::assertSame(1, $keys->at(1));
        static::assertSame(2, $keys->at(2));

        $vector = $this->create([]);
        $keys   = $vector->keys();

        static::assertInstanceOf($this->vectorClass, $keys);
        static::assertCount(0, $keys);
    }

    public function testFilter(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(static fn (string $item) => Str\contains($item, 'b'));

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotSame($vector, $filtered);
        static::assertContains('bar', $filtered);
        static::assertContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(2, $filtered);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(static fn (string $item) => Str\contains($item, 'hello'));

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testFilterWithKey(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(static fn (int $k, string $v) => 'foo' === $v || 3 === $k);

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotSame($vector, $filtered);
        static::assertContains('foo', $filtered);
        static::assertContains('qux', $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertCount(2, $filtered);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(static fn (int $k, string $v) => 4 === $k);

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testMap(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->map(static fn (string $item) => Str\uppercase($item));

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertSame([
            'FOO',
            'BAR',
            'BAZ',
            'QUX',
        ], $mapped->toArray());
        static::assertNotSame($vector, $mapped);
        static::assertCount(4, $mapped);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->map(static fn (string $item) => $item);

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertNotSame($vector, $mapped);
        static::assertSame($vector->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }

    public function testMapWithKey(): void
    {
        $vector = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $vector->mapWithKey(static fn (int $k, string $v) => Str\format('%s ( %d )', $v, $k));

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertSame([
            'foo ( 0 )',
            'bar ( 1 )',
            'baz ( 2 )',
            'qux ( 3 )',
        ], $mapped->toArray());
        static::assertNotSame($vector, $mapped);
        static::assertCount(4, $mapped);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->mapWithKey(static fn (int $k, string $v) => $k);

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertNotSame($vector, $mapped);
        static::assertSame($vector->keys()->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);

        $mapped = $vector->mapWithKey(static fn (int $k, string $v) => $v);

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertNotSame($vector, $mapped);
        static::assertSame($vector->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }

    public function testFirst(): void
    {
        $vector = $this->create([]);
        static::assertNull($vector->first());

        $vector = $this->create([null]);
        static::assertNull($vector->first());

        $vector = $this->create(['foo']);
        static::assertSame('foo', $vector->first());

        $vector = $this->create(['bar', 'qux']);
        static::assertSame('bar', $vector->first());
    }

    public function testFirstKey(): void
    {
        $vector = $this->create([]);
        static::assertNull($vector->firstKey());

        $vector = $this->create([null]);
        static::assertSame(0, $vector->firstKey());

        $vector = $this->create(['foo']);
        static::assertSame(0, $vector->firstKey());

        $vector = $this->create(['bar', 'qux']);
        static::assertSame(0, $vector->firstKey());
    }

    public function testLast(): void
    {
        $vector = $this->create([]);
        static::assertNull($vector->last());

        $vector = $this->create([null]);
        static::assertNull($vector->last());

        $vector = $this->create(['foo']);
        static::assertSame('foo', $vector->last());

        $vector = $this->create(['bar', 'qux']);
        static::assertSame('qux', $vector->last());
    }

    public function testLastKey(): void
    {
        $vector = $this->create([]);
        static::assertNull($vector->lastKey());

        $vector = $this->create([null]);
        static::assertSame(0, $vector->lastKey());

        $vector = $this->create(['foo']);
        static::assertSame(0, $vector->lastKey());

        $vector = $this->create(['bar', 'qux']);
        static::assertSame(1, $vector->lastKey());
    }

    public function testLinearSearch(): void
    {
        $vector = $this->create([]);
        static::assertNull($vector->linearSearch('foo'));

        $vector = $this->create([
            'foo',
            'bar',
        ]);
        static::assertSame(0, $vector->linearSearch('foo'));
        static::assertSame(1, $vector->linearSearch('bar'));
        static::assertNull($vector->linearSearch('baz'));
        static::assertNull($vector->linearSearch('qux'));
    }

    public function testZip(): void
    {
        $vector = $this->create([]);
        $zipped = $vector->zip([]);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(0, $zipped);

        $vector = $this->create([]);
        $zipped = $vector->zip([1, 2]);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(0, $zipped);

        $vector = $this->create(['foo', 'bar']);
        $zipped = $vector->zip([]);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(0, $zipped);

        $vector = $this->create(['foo', 'bar']);
        $zipped = $vector->zip(['baz', 'qux']);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['foo', 'baz'], $zipped->at(0));
        static::assertSame(['bar', 'qux'], $zipped->at(1));

        $vector = $this->create(['foo', 'bar', 'baz', 'qux']);
        $zipped = $vector->zip(['hello', 'world']);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['foo', 'hello'], $zipped->at(0));
        static::assertSame(['bar', 'world'], $zipped->at(1));

        $vector = $this->create(['hello', 'world']);
        $zipped = $vector->zip(['foo', 'bar', 'baz', 'qux']);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['hello', 'foo'], $zipped->at(0));
        static::assertSame(['world', 'bar'], $zipped->at(1));
    }

    public function testTake(): void
    {
        $vector = $this->create([]);
        $rest   = $vector->take(2);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->take(4);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->take(1);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at(0));
    }

    public function testTakeWhile(): void
    {
        $vector = $this->create([]);
        $rest   = $vector->takeWhile(static fn ($v) => false);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create([]);
        $rest   = $vector->takeWhile(static fn ($v) => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->takeWhile(static fn ($v) => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->takeWhile(static fn ($v) => 'bar' === $v);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at(0));
    }

    public function testDrop(): void
    {
        $vector = $this->create([]);
        $rest   = $vector->drop(2);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->drop(4);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->drop(1);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at(0));

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->drop(0);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $vector = $this->create([]);
        $rest   = $vector->dropWhile(static fn ($v) => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create([]);
        $rest   = $vector->dropWhile(static fn ($v) => false);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->dropWhile(static fn ($v) => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->dropWhile(static fn ($v) => false);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest   = $vector->dropWhile(static fn ($v) => 'bar' === $v);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at(0));
    }

    public function testSlice(): void
    {
        $vector = $this->create([
            'foo',
            'foo',
            'bar',
            'bar',
            'baz',
            'baz',
            'qux',
            'qux',
        ]);

        $slice1 = $vector->slice(0, 1);
        static::assertInstanceOf($this->vectorClass, $slice1);
        static::assertNotSame($slice1, $vector);
        static::assertCount(1, $slice1);
        static::assertSame('foo', $slice1->at(0));

        $slice2 = $vector->slice(2, 4);
        static::assertInstanceOf($this->vectorClass, $slice1);
        static::assertNotSame($slice2, $vector);
        static::assertCount(4, $slice2);
        static::assertSame([
            'bar',
            'bar',
            'baz',
            'baz',
        ], $slice2->toArray());
    }

    public function testAt(): void
    {
        $vector = $this->create([
            'hello',
            'world',
        ]);

        static::assertSame('hello', $vector->at(0));
        static::assertSame('world', $vector->at(1));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (2) is out-of-bounds.');

        $vector->at(2);
    }

    public function testContains(): void
    {
        $vector = $this->create([
            'hello',
            'world',
        ]);

        static::assertTrue($vector->contains(0));
        static::assertTrue($vector->contains(1));
        static::assertFalse($vector->contains(2));
    }

    public function testGet(): void
    {
        $vector = $this->create([
            'hello',
            'world',
        ]);

        static::assertSame('hello', $vector->get(0));
        static::assertSame('world', $vector->get(1));
        static::assertNull($vector->get(2));
    }

    public function testChunk(): void
    {
        $map = $this->create(['foo', 'bar', 'baz']);

        $chunks = $map->chunk(2);

        static::assertCount(2, $chunks);
        static::assertSame(['foo', 'bar'], $chunks->at(0)->toArray());
        static::assertSame(['baz'], $chunks->at(1)->toArray());

        $chunks = $map->chunk(1);

        static::assertCount(3, $chunks);
        static::assertSame(['foo'], $chunks->at(0)->toArray());
        static::assertSame(['bar'], $chunks->at(1)->toArray());
        static::assertSame(['baz'], $chunks->at(2)->toArray());
    }

    /**
     * @template     T
     *
     * @param iterable<T> $items
     *
     * @return IVector<T>
     */
    abstract protected function create(iterable $items): VectorInterface;
}
