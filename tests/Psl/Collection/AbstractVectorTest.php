<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

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
     * @psalm-var class-string<IVector>
     */
    protected string $vectorClass = VectorInterface::class;

    public function testIsEmpty(): void
    {
        self::assertTrue($this->create([])->isEmpty());
        self::assertFalse($this->create(['foo', 'bar'])->isEmpty());
        self::assertEmpty($this->create([null])->isEmpty());
    }

    public function testCount(): void
    {
        self::assertCount(0, $this->create([]));
        self::assertCount(2, $this->create(['foo', 'bar']));
        self::assertSame(5, $this->create([
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

        self::assertInstanceOf($this->vectorClass, $values);

        self::assertCount(3, $values);

        self::assertSame(1, $values->at(0));
        self::assertSame(2, $values->at(1));
        self::assertSame(3, $values->at(2));

        $vector = $this->create([]);
        $values = $vector->values();
        self::assertInstanceOf($this->vectorClass, $values);

        self::assertCount(0, $values);
    }

    public function testKeys(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
        ]);
        $keys = $vector->keys();

        self::assertInstanceOf($this->vectorClass, $keys);
        self::assertCount(3, $keys);
        self::assertSame(0, $keys->at(0));
        self::assertSame(1, $keys->at(1));
        self::assertSame(2, $keys->at(2));

        $vector = $this->create([]);
        $keys = $vector->keys();

        self::assertInstanceOf($this->vectorClass, $keys);
        self::assertCount(0, $keys);
    }

    public function testFilter(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(fn (string $item) => Str\contains($item, 'b'));

        self::assertInstanceOf($this->vectorClass, $filtered);
        self::assertNotSame($vector, $filtered);
        self::assertContains('bar', $filtered);
        self::assertContains('baz', $filtered);
        self::assertNotContains('foo', $filtered);
        self::assertNotContains('qux', $filtered);
        self::assertCount(2, $filtered);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(fn (string $item) => Str\contains($item, 'hello'));

        self::assertInstanceOf($this->vectorClass, $filtered);
        self::assertNotContains('bar', $filtered);
        self::assertNotContains('baz', $filtered);
        self::assertNotContains('foo', $filtered);
        self::assertNotContains('qux', $filtered);
        self::assertCount(0, $filtered);
    }

    public function testFilterWithKey(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(fn (int $k, string $v) => 'foo' === $v || 3 === $k);

        self::assertInstanceOf($this->vectorClass, $filtered);
        self::assertNotSame($vector, $filtered);
        self::assertContains('foo', $filtered);
        self::assertContains('qux', $filtered);
        self::assertNotContains('bar', $filtered);
        self::assertNotContains('baz', $filtered);
        self::assertCount(2, $filtered);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(fn (int $k, string $v) => 4 === $k);

        self::assertInstanceOf($this->vectorClass, $filtered);
        self::assertNotContains('bar', $filtered);
        self::assertNotContains('baz', $filtered);
        self::assertNotContains('foo', $filtered);
        self::assertNotContains('qux', $filtered);
        self::assertCount(0, $filtered);
    }

    public function testMap(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->map(fn (string $item) => Str\uppercase($item));

        self::assertInstanceOf($this->vectorClass, $mapped);
        self::assertSame([
            'FOO',
            'BAR',
            'BAZ',
            'QUX',
        ], $mapped->toArray());
        self::assertNotSame($vector, $mapped);
        self::assertCount(4, $mapped);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->map(fn (string $item) => $item);

        self::assertInstanceOf($this->vectorClass, $mapped);
        self::assertNotSame($vector, $mapped);
        self::assertSame($vector->toArray(), $mapped->toArray());
        self::assertCount(4, $mapped);
    }

    public function testMapWithKey(): void
    {
        $vector = $this->create([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $vector->mapWithKey(fn (int $k, string $v) => Str\format('%s ( %d )', $v, $k));

        self::assertInstanceOf($this->vectorClass, $mapped);
        self::assertSame([
            'foo ( 0 )',
            'bar ( 1 )',
            'baz ( 2 )',
            'qux ( 3 )',
        ], $mapped->toArray());
        self::assertNotSame($vector, $mapped);
        self::assertCount(4, $mapped);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->mapWithKey(fn (int $k, string $v) => $k);

        self::assertInstanceOf($this->vectorClass, $mapped);
        self::assertNotSame($vector, $mapped);
        self::assertSame($vector->keys()->toArray(), $mapped->toArray());
        self::assertCount(4, $mapped);

        $mapped = $vector->mapWithKey(fn (int $k, string $v) => $v);

        self::assertInstanceOf($this->vectorClass, $mapped);
        self::assertNotSame($vector, $mapped);
        self::assertSame($vector->toArray(), $mapped->toArray());
        self::assertCount(4, $mapped);
    }

    public function testFirst(): void
    {
        $vector = $this->create([]);
        self::assertNull($vector->first());

        $vector = $this->create([null]);
        self::assertNull($vector->first());

        $vector = $this->create(['foo']);
        self::assertSame('foo', $vector->first());

        $vector = $this->create(['bar', 'qux']);
        self::assertSame('bar', $vector->first());
    }

    public function testFirstKey(): void
    {
        $vector = $this->create([]);
        self::assertNull($vector->firstKey());

        $vector = $this->create([null]);
        self::assertSame(0, $vector->firstKey());

        $vector = $this->create(['foo']);
        self::assertSame(0, $vector->firstKey());

        $vector = $this->create(['bar', 'qux']);
        self::assertSame(0, $vector->firstKey());
    }

    public function testLast(): void
    {
        $vector = $this->create([]);
        self::assertNull($vector->last());

        $vector = $this->create([null]);
        self::assertNull($vector->last());

        $vector = $this->create(['foo']);
        self::assertSame('foo', $vector->last());

        $vector = $this->create(['bar', 'qux']);
        self::assertSame('qux', $vector->last());
    }

    public function testLastKey(): void
    {
        $vector = $this->create([]);
        self::assertNull($vector->lastKey());

        $vector = $this->create([null]);
        self::assertSame(0, $vector->lastKey());

        $vector = $this->create(['foo']);
        self::assertSame(0, $vector->lastKey());

        $vector = $this->create(['bar', 'qux']);
        self::assertSame(1, $vector->lastKey());
    }

    public function testLinearSearch(): void
    {
        $vector = $this->create([]);
        self::assertNull($vector->linearSearch('foo'));

        $vector = $this->create([
            'foo',
            'bar',
        ]);
        self::assertSame(0, $vector->linearSearch('foo'));
        self::assertSame(1, $vector->linearSearch('bar'));
        self::assertNull($vector->linearSearch('baz'));
        self::assertNull($vector->linearSearch('qux'));
    }

    public function testZip(): void
    {
        $vector = $this->create([]);
        $zipped = $vector->zip([]);
        self::assertInstanceOf($this->vectorClass, $zipped);
        self::assertCount(0, $zipped);

        $vector = $this->create([]);
        $zipped = $vector->zip([1, 2]);
        self::assertInstanceOf($this->vectorClass, $zipped);
        self::assertCount(0, $zipped);

        $vector = $this->create(['foo', 'bar']);
        $zipped = $vector->zip([]);
        self::assertInstanceOf($this->vectorClass, $zipped);
        self::assertCount(0, $zipped);

        $vector = $this->create(['foo', 'bar']);
        $zipped = $vector->zip(['baz', 'qux']);
        self::assertInstanceOf($this->vectorClass, $zipped);
        self::assertCount(2, $zipped);
        self::assertSame(['foo', 'baz'], $zipped->at(0));
        self::assertSame(['bar', 'qux'], $zipped->at(1));

        $vector = $this->create(['foo', 'bar', 'baz', 'qux']);
        $zipped = $vector->zip(['hello', 'world']);
        self::assertInstanceOf($this->vectorClass, $zipped);
        self::assertCount(2, $zipped);
        self::assertSame(['foo', 'hello'], $zipped->at(0));
        self::assertSame(['bar', 'world'], $zipped->at(1));

        $vector = $this->create(['hello', 'world']);
        $zipped = $vector->zip(['foo', 'bar', 'baz', 'qux']);
        self::assertInstanceOf($this->vectorClass, $zipped);
        self::assertCount(2, $zipped);
        self::assertSame(['hello', 'foo'], $zipped->at(0));
        self::assertSame(['world', 'bar'], $zipped->at(1));
    }

    public function testTake(): void
    {
        $vector = $this->create([]);
        $rest = $vector->take(2);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->take(4);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(2, $rest);
        self::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->take(1);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(1, $rest);
        self::assertSame('bar', $rest->at(0));
    }

    public function testTakeWhile(): void
    {
        $vector = $this->create([]);
        $rest = $vector->takeWhile(fn ($v) => false);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create([]);
        $rest = $vector->takeWhile(fn ($v) => true);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->takeWhile(fn ($v) => true);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(2, $rest);
        self::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->takeWhile(fn ($v) => 'bar' === $v);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(1, $rest);
        self::assertSame('bar', $rest->at(0));
    }

    public function testDrop(): void
    {
        $vector = $this->create([]);
        $rest = $vector->drop(2);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->drop(4);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->drop(1);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(1, $rest);
        self::assertSame('qux', $rest->at(0));

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->drop(0);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(2, $rest);
        self::assertSame($vector->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $vector = $this->create([]);
        $rest = $vector->dropWhile(fn ($v) => true);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create([]);
        $rest = $vector->dropWhile(fn ($v) => false);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->dropWhile(fn ($v) => true);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->dropWhile(fn ($v) => false);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(2, $rest);
        self::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->dropWhile(fn ($v) => 'bar' === $v);
        self::assertInstanceOf($this->vectorClass, $rest);
        self::assertNotSame($vector, $rest);
        self::assertCount(1, $rest);
        self::assertSame('qux', $rest->at(0));
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
        self::assertInstanceOf($this->vectorClass, $slice1);
        self::assertNotSame($slice1, $vector);
        self::assertCount(1, $slice1);
        self::assertSame('foo', $slice1->at(0));

        $slice2 = $vector->slice(2, 4);
        self::assertInstanceOf($this->vectorClass, $slice1);
        self::assertNotSame($slice2, $vector);
        self::assertCount(4, $slice2);
        self::assertSame([
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

        self::assertSame('hello', $vector->at(0));
        self::assertSame('world', $vector->at(1));

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

        self::assertTrue($vector->contains(0));
        self::assertTrue($vector->contains(1));
        self::assertFalse($vector->contains(2));
    }

    public function testGet(): void
    {
        $vector = $this->create([
            'hello',
            'world',
        ]);

        self::assertSame('hello', $vector->get(0));
        self::assertSame('world', $vector->get(1));
        self::assertNull($vector->get(2));
    }

    /**
     * @template     T
     *
     * @psalm-param  iterable<T> $items
     *
     * @psalm-return IVector<T>
     */
    abstract protected function create(iterable $items): VectorInterface;
}
