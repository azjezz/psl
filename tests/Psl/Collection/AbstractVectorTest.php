<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Collection\IVector;
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
    protected string $vectorClass = IVector::class;

    public function testIsEmpty(): void
    {
        $this->assertTrue($this->create([])->isEmpty());
        $this->assertFalse($this->create(['foo', 'bar'])->isEmpty());
        $this->assertEmpty($this->create([null])->isEmpty());
    }

    public function testCount(): void
    {
        $this->assertCount(0, $this->create([]));
        $this->assertCount(2, $this->create(['foo', 'bar']));
        $this->assertSame(5, $this->create([
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

        $this->assertInstanceOf($this->vectorClass, $values);

        $this->assertCount(3, $values);

        $this->assertSame(1, $values->at(0));
        $this->assertSame(2, $values->at(1));
        $this->assertSame(3, $values->at(2));

        $vector = $this->create([]);
        $values = $vector->values();
        $this->assertInstanceOf($this->vectorClass, $values);

        $this->assertCount(0, $values);
    }

    public function testKeys(): void
    {
        $vector = $this->create([
            'foo',
            'bar',
            'baz',
        ]);
        $keys = $vector->keys();

        $this->assertInstanceOf($this->vectorClass, $keys);
        $this->assertCount(3, $keys);
        $this->assertSame(0, $keys->at(0));
        $this->assertSame(1, $keys->at(1));
        $this->assertSame(2, $keys->at(2));

        $vector = $this->create([]);
        $keys = $vector->keys();

        $this->assertInstanceOf($this->vectorClass, $keys);
        $this->assertCount(0, $keys);
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

        $this->assertInstanceOf($this->vectorClass, $filtered);
        $this->assertNotSame($vector, $filtered);
        $this->assertContains('bar', $filtered);
        $this->assertContains('baz', $filtered);
        $this->assertNotContains('foo', $filtered);
        $this->assertNotContains('qux', $filtered);
        $this->assertCount(2, $filtered);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(fn (string $item) => Str\contains($item, 'hello'));

        $this->assertInstanceOf($this->vectorClass, $filtered);
        $this->assertNotContains('bar', $filtered);
        $this->assertNotContains('baz', $filtered);
        $this->assertNotContains('foo', $filtered);
        $this->assertNotContains('qux', $filtered);
        $this->assertCount(0, $filtered);
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

        $this->assertInstanceOf($this->vectorClass, $filtered);
        $this->assertNotSame($vector, $filtered);
        $this->assertContains('foo', $filtered);
        $this->assertContains('qux', $filtered);
        $this->assertNotContains('bar', $filtered);
        $this->assertNotContains('baz', $filtered);
        $this->assertCount(2, $filtered);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(fn (int $k, string $v) => 4 === $k);

        $this->assertInstanceOf($this->vectorClass, $filtered);
        $this->assertNotContains('bar', $filtered);
        $this->assertNotContains('baz', $filtered);
        $this->assertNotContains('foo', $filtered);
        $this->assertNotContains('qux', $filtered);
        $this->assertCount(0, $filtered);
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

        $this->assertInstanceOf($this->vectorClass, $mapped);
        $this->assertSame([
            'FOO',
            'BAR',
            'BAZ',
            'QUX',
        ], $mapped->toArray());
        $this->assertNotSame($vector, $mapped);
        $this->assertCount(4, $mapped);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->map(fn (string $item) => $item);

        $this->assertInstanceOf($this->vectorClass, $mapped);
        $this->assertNotSame($vector, $mapped);
        $this->assertSame($vector->toArray(), $mapped->toArray());
        $this->assertCount(4, $mapped);
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

        $this->assertInstanceOf($this->vectorClass, $mapped);
        $this->assertSame([
            'foo ( 0 )',
            'bar ( 1 )',
            'baz ( 2 )',
            'qux ( 3 )',
        ], $mapped->toArray());
        $this->assertNotSame($vector, $mapped);
        $this->assertCount(4, $mapped);

        $vector = $this->create([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->mapWithKey(fn (int $k, string $v) => $k);

        $this->assertInstanceOf($this->vectorClass, $mapped);
        $this->assertNotSame($vector, $mapped);
        $this->assertSame($vector->keys()->toArray(), $mapped->toArray());
        $this->assertCount(4, $mapped);

        $mapped = $vector->mapWithKey(fn (int $k, string $v) => $v);

        $this->assertInstanceOf($this->vectorClass, $mapped);
        $this->assertNotSame($vector, $mapped);
        $this->assertSame($vector->toArray(), $mapped->toArray());
        $this->assertCount(4, $mapped);
    }

    public function testFirst(): void
    {
        $vector = $this->create([]);
        $this->assertNull($vector->first());

        $vector = $this->create([null]);
        $this->assertNull($vector->first());

        $vector = $this->create(['foo']);
        $this->assertSame('foo', $vector->first());

        $vector = $this->create(['bar', 'qux']);
        $this->assertSame('bar', $vector->first());
    }

    public function testFirstKey(): void
    {
        $vector = $this->create([]);
        $this->assertNull($vector->firstKey());

        $vector = $this->create([null]);
        $this->assertSame(0, $vector->firstKey());

        $vector = $this->create(['foo']);
        $this->assertSame(0, $vector->firstKey());

        $vector = $this->create(['bar', 'qux']);
        $this->assertSame(0, $vector->firstKey());
    }

    public function testLast(): void
    {
        $vector = $this->create([]);
        $this->assertNull($vector->last());

        $vector = $this->create([null]);
        $this->assertNull($vector->last());

        $vector = $this->create(['foo']);
        $this->assertSame('foo', $vector->last());

        $vector = $this->create(['bar', 'qux']);
        $this->assertSame('qux', $vector->last());
    }

    public function testLastKey(): void
    {
        $vector = $this->create([]);
        $this->assertNull($vector->lastKey());

        $vector = $this->create([null]);
        $this->assertSame(0, $vector->lastKey());

        $vector = $this->create(['foo']);
        $this->assertSame(0, $vector->lastKey());

        $vector = $this->create(['bar', 'qux']);
        $this->assertSame(1, $vector->lastKey());
    }

    public function testLinearSearch(): void
    {
        $vector = $this->create([]);
        $this->assertNull($vector->linearSearch('foo'));

        $vector = $this->create([
            'foo',
            'bar',
        ]);
        $this->assertSame(0, $vector->linearSearch('foo'));
        $this->assertSame(1, $vector->linearSearch('bar'));
        $this->assertNull($vector->linearSearch('baz'));
        $this->assertNull($vector->linearSearch('qux'));
    }

    public function testZip(): void
    {
        $vector = $this->create([]);
        $zipped = $vector->zip([]);
        $this->assertInstanceOf($this->vectorClass, $zipped);
        $this->assertCount(0, $zipped);

        $vector = $this->create([]);
        $zipped = $vector->zip([1, 2]);
        $this->assertInstanceOf($this->vectorClass, $zipped);
        $this->assertCount(0, $zipped);

        $vector = $this->create(['foo', 'bar']);
        $zipped = $vector->zip([]);
        $this->assertInstanceOf($this->vectorClass, $zipped);
        $this->assertCount(0, $zipped);

        $vector = $this->create(['foo', 'bar']);
        $zipped = $vector->zip(['baz', 'qux']);
        $this->assertInstanceOf($this->vectorClass, $zipped);
        $this->assertCount(2, $zipped);
        $this->assertSame(['foo', 'baz'], $zipped->at(0));
        $this->assertSame(['bar', 'qux'], $zipped->at(1));

        $vector = $this->create(['foo', 'bar', 'baz', 'qux']);
        $zipped = $vector->zip(['hello', 'world']);
        $this->assertInstanceOf($this->vectorClass, $zipped);
        $this->assertCount(2, $zipped);
        $this->assertSame(['foo', 'hello'], $zipped->at(0));
        $this->assertSame(['bar', 'world'], $zipped->at(1));

        $vector = $this->create(['hello', 'world']);
        $zipped = $vector->zip(['foo', 'bar', 'baz', 'qux']);
        $this->assertInstanceOf($this->vectorClass, $zipped);
        $this->assertCount(2, $zipped);
        $this->assertSame(['hello', 'foo'], $zipped->at(0));
        $this->assertSame(['world', 'bar'], $zipped->at(1));
    }

    public function testTake(): void
    {
        $vector = $this->create([]);
        $rest = $vector->take(2);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->take(4);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->take(1);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('bar', $rest->at(0));
    }

    public function testTakeWhile(): void
    {
        $vector = $this->create([]);
        $rest = $vector->takeWhile(fn ($v) => false);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create([]);
        $rest = $vector->takeWhile(fn ($v) => true);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->takeWhile(fn ($v) => true);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->takeWhile(fn ($v) => 'bar' === $v);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('bar', $rest->at(0));
    }

    public function testDrop(): void
    {
        $vector = $this->create([]);
        $rest = $vector->drop(2);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->drop(4);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->drop(1);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('qux', $rest->at(0));

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->drop(0);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($vector->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $vector = $this->create([]);
        $rest = $vector->dropWhile(fn ($v) => true);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create([]);
        $rest = $vector->dropWhile(fn ($v) => false);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->dropWhile(fn ($v) => true);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(0, $rest);

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->dropWhile(fn ($v) => false);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(2, $rest);
        $this->assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create(['bar', 'qux']);
        $rest = $vector->dropWhile(fn ($v) => 'bar' === $v);
        $this->assertInstanceOf($this->vectorClass, $rest);
        $this->assertNotSame($vector, $rest);
        $this->assertCount(1, $rest);
        $this->assertSame('qux', $rest->at(0));
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
        $this->assertInstanceOf($this->vectorClass, $slice1);
        $this->assertNotSame($slice1, $vector);
        $this->assertCount(1, $slice1);
        $this->assertSame('foo', $slice1->at(0));

        $slice2 = $vector->slice(2, 4);
        $this->assertInstanceOf($this->vectorClass, $slice1);
        $this->assertNotSame($slice2, $vector);
        $this->assertCount(4, $slice2);
        $this->assertSame([
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

        $this->assertSame('hello', $vector->at(0));
        $this->assertSame('world', $vector->at(1));

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

        $this->assertTrue($vector->contains(0));
        $this->assertTrue($vector->contains(1));
        $this->assertFalse($vector->contains(2));
    }

    public function testGet(): void
    {
        $vector = $this->create([
            'hello',
            'world',
        ]);

        $this->assertSame('hello', $vector->get(0));
        $this->assertSame('world', $vector->get(1));
        $this->assertNull($vector->get(2));
    }

    /**
     * @template     T
     *
     * @psalm-param  iterable<T> $items
     *
     * @psalm-return IVector<T>
     */
    abstract protected function create(iterable $items): IVector;
}
