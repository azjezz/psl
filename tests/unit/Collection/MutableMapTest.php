<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection;
use Psl\Collection\Exception;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;

final class MutableMapTest extends AbstractMapTest
{
    /**
     * @var class-string<MutableMap>
     */
    protected string $mapClass = MutableMap::class;

    /**
     * @var class-string<MutableVector>
     */
    protected string $vectorClass = MutableVector::class;

    public function testClear(): void
    {
        $map = $this->create(['foo' => 'bar']);
        $cleared = $map->clear();

        static::assertSame($cleared, $map);
        static::assertCount(0, $map);
    }

    public function testSet(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $modified = $map->set('foo', 'foo')->set('bar', 'bar')->set('baz', 'baz');

        static::assertSame($modified, $map);

        static::assertSame('foo', $map->at('foo'));
        static::assertSame('bar', $map->at('bar'));
        static::assertSame('baz', $map->at('baz'));

        $this->expectException(Collection\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (qux) was out-of-bounds.');

        $map->set('qux', 'qux');
    }

    public function testSetAll(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $modified = $map->setAll([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ]);

        static::assertSame($modified, $map);

        static::assertSame('foo', $map->at('foo'));
        static::assertSame('bar', $map->at('bar'));
        static::assertSame('baz', $map->at('baz'));

        $this->expectException(Collection\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (qux) was out-of-bounds.');

        $map->setAll(['qux' => 'qux']);
    }

    public function testAdd(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $modified = $map
            ->add('foo', 'foo')
            ->add('bar', 'bar')
            ->add('baz', 'baz')
            ->add('qux', 'qux');

        static::assertSame($modified, $map);

        static::assertSame('foo', $map->at('foo'));
        static::assertSame('bar', $map->at('bar'));
        static::assertSame('baz', $map->at('baz'));
        static::assertSame('qux', $map->at('qux'));
    }

    public function testAddAll(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $modified = $map->addAll([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
            'qux' => 'qux',
        ]);

        static::assertSame($modified, $map);

        static::assertSame('foo', $map->at('foo'));
        static::assertSame('bar', $map->at('bar'));
        static::assertSame('baz', $map->at('baz'));
        static::assertSame('qux', $map->at('qux'));
    }

    public function testRemove(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $modified = $map->remove('foo')->remove('bar');

        static::assertSame($modified, $map);
        static::assertCount(1, $map);
        static::assertSame('qux', $map->get('baz'));
        static::assertNull($map->get('foo'));
        static::assertNull($map->get('bar'));
    }

    public function testArrayAccess(): void
    {
        $map = $this->create([
            'foo' => '1',
            'bar' => '2',
            'baz' => '3',
        ]);

        static::assertTrue(isset($map['foo']));
        static::assertSame('1', $map['foo']);

        unset($map['foo']);
        static::assertFalse(isset($map['foo']));

        $map['foo'] = '2';
        static::assertTrue(isset($map['foo']));
        static::assertSame('2', $map['foo']);

        $map['qux'] = '4';
        static::assertTrue(isset($map['qux']));
        static::assertCount(4, $map);

        $map[124] = 'v';
        static::assertTrue(isset($map[124]));
        static::assertSame('v', $map[124]);
        static::assertCount(5, $map);

        unset($map[124]);

        $this->expectException(Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (124) was out-of-bounds.');

        $map[124];
    }

    public function testOffsetSetThrowsForInvalidOffsetType(): void
    {
        $map = $this->create([
            'foo' => '1',
            'bar' => '2',
            'baz' => '3',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid map write offset type, expected a string or an integer.');

        $map[false] = 'qux';
    }

    public function testOffsetIssetThrowsForInvalidOffsetType(): void
    {
        $map = $this->create([
            'foo' => '1',
            'bar' => '2',
            'baz' => '3',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid map read offset type, expected a string or an integer.');

        isset($map[false]);
    }

    public function testOffsetUnsetThrowsForInvalidOffsetType(): void
    {
        $map = $this->create([
            'foo' => '1',
            'bar' => '2',
            'baz' => '3',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid map read offset type, expected a string or an integer.');

        unset($map[false]);
    }

    public function testOffsetGetThrowsForInvalidOffsetType(): void
    {
        $map = $this->create([
            'foo' => '1',
            'bar' => '2',
            'baz' => '3',
        ]);

        $this->expectException(Exception\InvalidOffsetException::class);
        $this->expectExceptionMessage('Invalid map read offset type, expected a string or an integer.');

        $map[false];
    }

    public function testFromItems(): void
    {
        $map = MutableMap::fromItems([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        static::assertSame('bar', $map->at('foo'));
        static::assertSame('baz', $map->at('bar'));
        static::assertSame('qux', $map->at('baz'));
    }

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @param iterable<Tk, Tv> $items
     *
     * @return MutableMap<Tk, Tv>
     */
    #[\Override]
    protected function create(iterable $items): MutableMap
    {
        return MutableMap::fromArray($items);
    }
}
