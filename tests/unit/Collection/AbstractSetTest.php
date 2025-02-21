<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Collection\SetInterface;
use Psl\Str;

abstract class AbstractSetTest extends TestCase
{
    /**
     * The set class used for values, keys .. etc.
     *
     * @var class-string<SetInterface>
     */
    protected string $setClass = SetInterface::class;

    public function testIsEmpty(): void
    {
        static::assertTrue($this->default()->isEmpty());
        static::assertTrue($this->createFromList([])->isEmpty());
        static::assertFalse($this->createFromList(['foo', 'bar'])->isEmpty());
        static::assertFalse($this->createFromList([1])->isEmpty());
    }

    public function testCount(): void
    {
        static::assertCount(0, $this->default());
        static::assertCount(0, $this->createFromList([]));
        static::assertCount(2, $this->createFromList(['foo', 'bar']));
        static::assertSame(
            5,
            $this->createFromList([
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
        $vector = $this->createFromList([1, 2, 3]);

        $values = $vector->values();

        static::assertCount(3, $values);

        static::assertSame(1, $values->at(0));
        static::assertSame(2, $values->at(1));
        static::assertSame(3, $values->at(2));

        $vector = $this->createFromList([]);
        $values = $vector->values();

        static::assertCount(0, $values);
    }

    public function testJsonSerialize(): void
    {
        $vector = $this->createFromList(['foo', 'bar', 'baz']);

        $array = $vector->jsonSerialize();

        static::assertSame(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'], $array);
    }

    public function testKeys(): void
    {
        $vector = $this->createFromList([
            'foo',
            'bar',
            'baz',
        ]);
        $keys = $vector->keys();

        static::assertCount(3, $keys);
        static::assertSame('foo', $keys->at(0));
        static::assertSame('bar', $keys->at(1));
        static::assertSame('baz', $keys->at(2));

        $vector = $this->createFromList([]);
        $keys = $vector->keys();

        static::assertCount(0, $keys);
    }

    public function testFilter(): void
    {
        $vector = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(static fn(string $item): bool => Str\contains($item, 'b'));

        static::assertInstanceOf($this->setClass, $filtered);
        static::assertNotSame($vector, $filtered);
        static::assertContains('bar', $filtered);
        static::assertContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(2, $filtered);

        $vector = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filter(static fn(string $item): bool => Str\contains($item, 'hello'));

        static::assertInstanceOf($this->setClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testFilterWithKey(): void
    {
        $vector = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(static fn(string $item): bool => Str\contains($item, 'b'));

        static::assertInstanceOf($this->setClass, $filtered);
        static::assertNotSame($vector, $filtered);
        static::assertContains('bar', $filtered);
        static::assertContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(2, $filtered);

        $vector = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $filtered = $vector->filterWithKey(static fn(string $item): bool => Str\contains($item, 'hello'));

        static::assertInstanceOf($this->setClass, $filtered);
        static::assertNotContains('bar', $filtered);
        static::assertNotContains('baz', $filtered);
        static::assertNotContains('foo', $filtered);
        static::assertNotContains('qux', $filtered);
        static::assertCount(0, $filtered);
    }

    public function testMap(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $set->map(static fn(string $item): string => Str\uppercase($item));

        static::assertInstanceOf($this->setClass, $mapped);
        static::assertSame(
            [
                'FOO' => 'FOO',
                'BAR' => 'BAR',
                'BAZ' => 'BAZ',
                'QUX' => 'QUX',
            ],
            $mapped->toArray(),
        );
        static::assertNotSame($set, $mapped);
        static::assertCount(4, $mapped);

        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $set->map(static fn(string $item): string => $item);

        static::assertInstanceOf($this->setClass, $mapped);
        static::assertNotSame($set, $mapped);
        static::assertSame($set->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }
    public function testMapWithKey(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $set->mapWithKey(static fn(string $item): string => Str\uppercase($item));

        static::assertInstanceOf($this->setClass, $mapped);
        static::assertSame(
            [
                'FOO' => 'FOO',
                'BAR' => 'BAR',
                'BAZ' => 'BAZ',
                'QUX' => 'QUX',
            ],
            $mapped->toArray(),
        );
        static::assertNotSame($set, $mapped);
        static::assertCount(4, $mapped);

        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $mapped = $set->mapWithKey(static fn(string $item): string => $item);

        static::assertInstanceOf($this->setClass, $mapped);
        static::assertNotSame($set, $mapped);
        static::assertSame($set->toArray(), $mapped->toArray());
        static::assertCount(4, $mapped);
    }
    public function testZip(): void
    {
        $set = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
        ]);

        $other = $this->createFromList([
            'hello',
            'world',
            'foo',
            'bar',
        ]);

        $this->expectException(Collection\Exception\RuntimeException::class);

        $set->zip($other->toArray());
    }

    public function testFirst(): void
    {
        $vector = $this->createFromList([]);
        static::assertNull($vector->first());

        $vector = $this->createFromList(['foo']);
        static::assertSame('foo', $vector->first());

        $vector = $this->createFromList(['bar', 'qux']);
        static::assertSame('bar', $vector->first());
    }

    public function testFirstKey(): void
    {
        $vector = $this->createFromList([]);
        static::assertNull($vector->firstKey());

        $vector = $this->createFromList(['foo']);
        static::assertSame('foo', $vector->firstKey());

        $vector = $this->createFromList(['bar', 'qux']);
        static::assertSame('bar', $vector->firstKey());
    }

    public function testLast(): void
    {
        $vector = $this->createFromList([]);
        static::assertNull($vector->last());

        $vector = $this->createFromList(['foo']);
        static::assertSame('foo', $vector->last());

        $vector = $this->createFromList(['bar', 'qux']);
        static::assertSame('qux', $vector->last());
    }

    public function testLastKey(): void
    {
        $vector = $this->createFromList([]);
        static::assertNull($vector->lastKey());

        $vector = $this->createFromList(['foo']);
        static::assertSame('foo', $vector->lastKey());

        $vector = $this->createFromList(['bar', 'qux']);
        static::assertSame('qux', $vector->lastKey());
    }

    public function testLinearSearch(): void
    {
        $vector = $this->createFromList([]);
        static::assertNull($vector->linearSearch('foo'));

        $vector = $this->createFromList([
            'foo',
            'bar',
        ]);
        static::assertSame('foo', $vector->linearSearch('foo'));
        static::assertSame('bar', $vector->linearSearch('bar'));
        static::assertNull($vector->linearSearch('baz'));
        static::assertNull($vector->linearSearch('qux'));
    }

    public function testTake(): void
    {
        $set = $this->default();
        $rest = $set->take(2);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->take(4);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(2, $rest);
        static::assertSame($set->toArray(), $rest->toArray());

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->take(1);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at('bar'));
    }

    public function testTakeWhile(): void
    {
        $set = $this->default();
        $rest = $set->takeWhile(static fn(string $_v): bool => false);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->default();
        $rest = $set->takeWhile(static fn(string $_v): bool => true);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->takeWhile(static fn(string $_v): bool => true);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(2, $rest);
        static::assertSame($set->toArray(), $rest->toArray());

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->takeWhile(static fn(string $v): bool => 'bar' === $v);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(1, $rest);
        static::assertSame('bar', $rest->at('bar'));
    }

    public function testDrop(): void
    {
        $set = $this->default();
        $rest = $set->drop(2);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->drop(4);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->drop(1);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at('qux'));

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->drop(0);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(2, $rest);
        static::assertSame($set->toArray(), $rest->toArray());
    }

    public function testDropWhile(): void
    {
        $set = $this->default();
        $rest = $set->dropWhile(static fn(string $_v): bool => true);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->default();
        $rest = $set->dropWhile(static fn(string $_v): bool => false);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->dropWhile(static fn(string $_v): bool => true);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(0, $rest);

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->dropWhile(static fn(string $_v): bool => false);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(2, $rest);
        static::assertSame($set->toArray(), $rest->toArray());

        $set = $this->createFromList(['bar', 'qux']);
        $rest = $set->dropWhile(static fn(string $v): bool => 'bar' === $v);
        static::assertInstanceOf($this->setClass, $rest);
        static::assertNotSame($set, $rest);
        static::assertCount(1, $rest);
        static::assertSame('qux', $rest->at('qux'));
    }

    public function testSlice(): void
    {
        $vector = $this->createFromList([
            'foo',
            'bar',
            'baz',
            'qux',
            'hax',
            'dax',
            'lax',
            'fax',
        ]);

        $slice1 = $vector->slice(0, 1);
        static::assertInstanceOf($this->setClass, $slice1);
        static::assertNotSame($slice1, $vector);
        static::assertCount(1, $slice1);
        static::assertSame('foo', $slice1->at('foo'));

        $slice2 = $vector->slice(2, 4);
        static::assertInstanceOf($this->setClass, $slice1);
        static::assertNotSame($slice2, $vector);
        static::assertCount(4, $slice2);
        static::assertSame(
            [
                'baz' => 'baz',
                'qux' => 'qux',
                'hax' => 'hax',
                'dax' => 'dax',
            ],
            $slice2->toArray(),
        );
    }

    public function testAt(): void
    {
        $set = $this->createFromList([
            'hello',
            'world',
        ]);

        static::assertSame('hello', $set->at('hello'));
        static::assertSame('world', $set->at('world'));

        $this->expectException(Collection\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (foo) was out-of-bounds.');

        $set->at('foo');
    }

    public function testContains(): void
    {
        $vector = $this->createFromList([
            'hello',
            'world',
        ]);

        static::assertTrue($vector->contains('hello'));
        static::assertTrue($vector->containsKey('hello'));
        static::assertTrue($vector->contains('world'));
        static::assertFalse($vector->contains('foo'));
        static::assertFalse($vector->containsKey('foo'));
    }

    public function testGet(): void
    {
        $vector = $this->createFromList([
            'hello',
            'world',
        ]);

        static::assertSame('hello', $vector->get('hello'));
        static::assertSame('world', $vector->get('world'));
        static::assertNull($vector->get('foo'));
    }

    public function testChunk(): void
    {
        $set = $this->createFromList(['foo', 'bar', 'baz']);

        $chunks = $set->chunk(2);

        static::assertCount(2, $chunks);
        static::assertSame(['foo' => 'foo', 'bar' => 'bar'], $chunks->at(0)->toArray());
        static::assertSame(['baz' => 'baz'], $chunks->at(1)->toArray());

        $chunks = $set->chunk(1);

        static::assertCount(3, $chunks);
        static::assertSame(['foo' => 'foo'], $chunks->at(0)->toArray());
        static::assertSame(['bar' => 'bar'], $chunks->at(1)->toArray());
        static::assertSame(['baz' => 'baz'], $chunks->at(2)->toArray());
    }

    protected function default(): SetInterface
    {
        return $this->setClass::default();
    }

    /**
     * @template T of array-key
     *
     * @param list<T> $items
     *
     * @return SetInterface<T>
     */
    abstract protected function createFromList(array $items): SetInterface;
}
