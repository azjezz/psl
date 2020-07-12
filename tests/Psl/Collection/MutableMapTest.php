<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use Psl\Collection\Map;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;
use Psl\Exception\InvariantViolationException;

/**
 * @covers \Psl\Collection\MutableMap
 */
final class MutableMapTest extends AbstractMapTest
{
    /**
     * @psalm-var class-string<MutableMap>
     */
    protected string $mapClass = MutableMap::class;

    /**
     * @psalm-var class-string<MutableVector>
     */
    protected string $vectorClass = MutableVector::class;

    public function testClear(): void
    {
        $map = $this->create(['foo' => 'bar']);
        $cleared = $map->clear();

        self::assertSame($cleared, $map);
        self::assertCount(0, $map);
    }

    public function testSet(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $modified = $map
            ->set('foo', 'foo')
            ->set('bar', 'bar')
            ->set('baz', 'baz');

        self::assertSame($modified, $map);

        self::assertSame('foo', $map->at('foo'));
        self::assertSame('bar', $map->at('bar'));
        self::assertSame('baz', $map->at('baz'));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (qux) is out-of-bounds.');

        $map->set('qux', 'qux');
    }

    public function testSetAll(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $modified = $map->setAll(new Map([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ]));

        self::assertSame($modified, $map);

        self::assertSame('foo', $map->at('foo'));
        self::assertSame('bar', $map->at('bar'));
        self::assertSame('baz', $map->at('baz'));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (qux) is out-of-bounds.');

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

        self::assertSame($modified, $map);

        self::assertSame('foo', $map->at('foo'));
        self::assertSame('bar', $map->at('bar'));
        self::assertSame('baz', $map->at('baz'));
        self::assertSame('qux', $map->at('qux'));
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

        self::assertSame($modified, $map);

        self::assertSame('foo', $map->at('foo'));
        self::assertSame('bar', $map->at('bar'));
        self::assertSame('baz', $map->at('baz'));
        self::assertSame('qux', $map->at('qux'));
    }

    public function testRemove(): void
    {
        $map = $this->create([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $modified = $map
            ->remove('foo')
            ->remove('bar');

        self::assertSame($modified, $map);
        self::assertCount(1, $map);
        self::assertSame('qux', $map->get('baz'));
        self::assertNull($map->get('foo'));
        self::assertNull($map->get('bar'));
    }

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @psalm-param  iterable<Tk, Tv> $items
     *
     * @psalm-return MutableMap<Tk, Tv>
     */
    protected function create(iterable $items): MutableMap
    {
        return new MutableMap($items);
    }
}
