<?php

declare(strict_types=1);

namespace Psl\Collection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Collection\VectorInterface;
use Psl\Str;

abstract class AbstractVectorTestCase extends TestCase
{
    /**
     * The Vector class used for values, keys .. etc.
     *
     * @var class-string<VectorInterface>
     */
    protected string $vectorClass = VectorInterface::class;

    public function testIsEmpty(): void
    {
        static::assertTrue($this->default()->isEmpty());
        static::assertTrue($this->create::<string>([])->isEmpty());
        static::assertFalse($this->create::<string>(['foo', 'bar'])->isEmpty());
        static::assertEmpty($this->create::<null>([null])->isEmpty());
    }

    public function testCount(): void
    {
        static::assertCount(0, $this->default());
        static::assertCount(0, $this->create::<string>([]));
        static::assertCount(2, $this->create::<string>(['foo', 'bar']));
        static::assertSame(
            5,
            $this->create::<string>([
                'foo',
                'bar',
                'baz',
                'qux',
                'hax', // ??
            ])->count(),
        );
    }

    public function testValues(): void
    {
        $vector = $this->create::<int>([1, 2, 3]);

        $values = $vector->values();

        static::assertInstanceOf($this->vectorClass, $values);

        static::assertCount(3, $values);

        static::assertSame(1, $values->at(0));
        static::assertSame(2, $values->at(1));
        static::assertSame(3, $values->at(2));

        $vector = $this->create::<int>([]);
        $values = $vector->values();
        static::assertInstanceOf($this->vectorClass, $values);

        static::assertCount(0, $values);
    }

    public function testJsonSerialize(): void
    {
        $vector = $this->create::<string>(['foo', 'bar', 'baz']);

        $array = $vector->jsonSerialize();

        static::assertSame(['foo', 'bar', 'baz'], $array);
    }

    public function testKeys(): void
    {
        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
        ]);
        $keys = $vector->keys();

        static::assertInstanceOf($this->vectorClass, $keys);
        static::assertCount(3, $keys);
        static::assertSame(0, $keys->at(0));
        static::assertSame(1, $keys->at(1));
        static::assertSame(2, $keys->at(2));

        $vector = $this->create::<string>([]);
        $keys = $vector->keys();

        static::assertInstanceOf($this->vectorClass, $keys);
        static::assertCount(0, $keys);
    }

    public function testFilter(): void
    {
        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(static fn(string $item): bool => Str\contains($item, 'b'));

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotSame($vector, $filtered);
        static::assertContains('bar', $filtered);
        static::assertContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(2, $filtered);

        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(static fn(string $item): bool => Str\contains($item, 'hello'));

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testFilterWithKey(): void
    {
        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(static fn(int $k, string $v): bool => 'foo' === $v || 3 === $k);

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotSame($vector, $filtered);
        static::assertContains('foo', $filtered);
        static::assertContains('qux', $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertCount(2, $filtered);

        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(static fn(int $k, string $_): bool => 4 === $k);

        static::assertInstanceOf($this->vectorClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testMap(): void
    {
        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->map::<string>(Str\uppercase(...));

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertSame(
            [
                'FOO',
                'BAR',
                'BAZ',
                'QUX',
            ],
            $mapped->toArray(),
        );
        static::assertNotSame($vector, $mapped);
        static::assertCount(4, $mapped);

        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->map::<string>(static fn(string $item): string => $item);

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertNotSame($vector, $mapped);
        static::assertSame($vector->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }

    public function testMapWithKey(): void
    {
        $vector = $this->create::<string>([
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
            3 => 'qux',
        ]);

        $mapped = $vector->mapWithKey::<string>(static fn(int $k, string $v): string => Str\format('%s ( %d )', $v, $k));

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertSame(
            [
                'foo ( 0 )',
                'bar ( 1 )',
                'baz ( 2 )',
                'qux ( 3 )',
            ],
            $mapped->toArray(),
        );
        static::assertNotSame($vector, $mapped);
        static::assertCount(4, $mapped);

        $vector = $this->create::<string>([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $vector->mapWithKey::<int>(static fn(int $k, string $_): int => $k);

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertNotSame($vector, $mapped);
        static::assertSame($vector->keys()->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);

        $mapped = $vector->mapWithKey::<string>(static fn(int $_, string $v): string => $v);

        static::assertInstanceOf($this->vectorClass, $mapped);
        static::assertNotSame($vector, $mapped);
        static::assertSame($vector->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }

    public function testFirst(): void
    {
        $vector = $this->create::<string>([]);
        static::assertNull($vector->first());

        $vector = $this->create::<null>([null]);
        static::assertNull($vector->first());

        $vector = $this->create::<string>(['foo']);
        static::assertSame('foo', $vector->first());

        $vector = $this->create::<string>(['bar', 'qux']);
        static::assertSame('bar', $vector->first());
    }

    public function testFirstKey(): void
    {
        $vector = $this->create::<string>([]);
        static::assertNull($vector->firstKey());

        $vector = $this->create::<null>([null]);
        static::assertSame(0, $vector->firstKey());

        $vector = $this->create::<string>(['foo']);
        static::assertSame(0, $vector->firstKey());

        $vector = $this->create::<string>(['bar', 'qux']);
        static::assertSame(0, $vector->firstKey());
    }

    public function testLast(): void
    {
        $vector = $this->create::<string>([]);
        static::assertNull($vector->last());

        $vector = $this->create::<null>([null]);
        static::assertNull($vector->last());

        $vector = $this->create::<string>(['foo']);
        static::assertSame('foo', $vector->last());

        $vector = $this->create::<string>(['bar', 'qux']);
        static::assertSame('qux', $vector->last());
    }

    public function testLastKey(): void
    {
        $vector = $this->create::<string>([]);
        static::assertNull($vector->lastKey());

        $vector = $this->create::<null>([null]);
        static::assertSame(0, $vector->lastKey());

        $vector = $this->create::<string>(['foo']);
        static::assertSame(0, $vector->lastKey());

        $vector = $this->create::<string>(['bar', 'qux']);
        static::assertSame(1, $vector->lastKey());
    }

    public function testLinearSearch(): void
    {
        $vector = $this->create::<string>([]);
        static::assertNull($vector->linearSearch('foo'));

        $vector = $this->create::<string>([
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
        $vector = $this->create::<string>([]);
        $zipped = $vector->zip::<string>([]);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(0, $zipped);

        $vector = $this->create::<string>([]);
        $zipped = $vector->zip::<int>([1, 2]);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(0, $zipped);

        $vector = $this->create::<string>(['foo', 'bar']);
        $zipped = $vector->zip::<string>([]);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(0, $zipped);

        $vector = $this->create::<string>(['foo', 'bar']);
        $zipped = $vector->zip::<string>(['baz', 'qux']);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['foo', 'baz'], $zipped->at(0));
        static::assertSame(['bar', 'qux'], $zipped->at(1));

        $vector = $this->create::<string>(['foo', 'bar', 'baz', 'qux']);
        $zipped = $vector->zip::<string>(['hello', 'world']);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['foo', 'hello'], $zipped->at(0));
        static::assertSame(['bar', 'world'], $zipped->at(1));

        $vector = $this->create::<string>(['hello', 'world']);
        $zipped = $vector->zip::<string>(['foo', 'bar', 'baz', 'qux']);
        static::assertInstanceOf($this->vectorClass, $zipped);
        static::assertCount(2, $zipped);
        static::assertSame(['hello', 'foo'], $zipped->at(0));
        static::assertSame(['world', 'bar'], $zipped->at(1));
    }

    public function testTake(): void
    {
        $vector = $this->create::<string>([]);
        $rest = $vector->take(2);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->take(4);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->take(1);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at(0));

        $vector = $this->create::<string>(['a', 'b', 'c', 'd']);
        $rest = $vector->take(2);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertCount(2, $rest);
        static::assertSame(['a', 'b'], $rest->toArray());
    }

    public function testTakeWhile(): void
    {
        $vector = $this->create::<string>([]);
        $rest = $vector->takeWhile(static fn(string $_): bool => false);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>([]);
        $rest = $vector->takeWhile(static fn(string $_): bool => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->takeWhile(static fn(string $_): bool => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->takeWhile(static fn(string $v): bool => 'bar' === $v);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at(0));
    }

    public function testDrop(): void
    {
        $vector = $this->create::<string>([]);
        $rest = $vector->drop(2);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->drop(4);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->drop(1);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at(0));

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->drop(0);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $vector = $this->create::<string>([]);
        $rest = $vector->dropWhile(static fn(string $_): bool => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>([]);
        $rest = $vector->dropWhile(static fn(string $_): bool => false);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->dropWhile(static fn(string $_): bool => true);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(0, $rest);

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->dropWhile(static fn(string $_): bool => false);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(2, $rest);
        static::assertSame($vector->toArray(), $rest->toArray());

        $vector = $this->create::<string>(['bar', 'qux']);
        $rest = $vector->dropWhile(static fn(string $v): bool => 'bar' === $v);
        static::assertInstanceOf($this->vectorClass, $rest);
        static::assertNotSame($vector, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at(0));
    }

    public function testSlice(): void
    {
        $vector = $this->create::<string>([
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
        static::assertSame(
            [
                'bar',
                'bar',
                'baz',
                'baz',
            ],
            $slice2->toArray(),
        );
    }

    public function testAt(): void
    {
        $vector = $this->create::<string>([
            'hello',
            'world',
        ]);

        static::assertSame('hello', $vector->at(0));
        static::assertSame('world', $vector->at(1));

        $this->expectException(Collection\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (2) was out-of-bounds.');

        $vector->at(2);
    }

    public function testContains(): void
    {
        $vector = $this->create::<string>([
            'hello',
            'world',
        ]);

        static::assertTrue($vector->contains(0));
        static::assertTrue($vector->contains(1));
        static::assertTrue($vector->containsKey(1));
        static::assertFalse($vector->contains(2));
        static::assertFalse($vector->containsKey(2));
    }

    public function testGet(): void
    {
        $vector = $this->create::<string>([
            'hello',
            'world',
        ]);

        static::assertSame('hello', $vector->get(0));
        static::assertSame('world', $vector->get(1));
        static::assertNull($vector->get(2));
    }

    public function testChunk(): void
    {
        $map = $this->create::<string>(['foo', 'bar', 'baz']);

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

    protected function default(): VectorInterface<mixed>
    {
        return $this->vectorClass::default();
    }

    /**
     * @param list<T> $items
     */
    abstract protected function create<T>(array $items): VectorInterface<T>;
}
