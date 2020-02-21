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

    public function testClear(): void
    {
        $map = $this->create(['foo' => 'bar']);
        $cleared = $map->clear();

        $this->assertSame($cleared, $map);
        $this->assertCount(0, $map);
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

        $this->assertSame($modified, $map);

        $this->assertSame('foo', $map->at('foo'));
        $this->assertSame('bar', $map->at('bar'));
        $this->assertSame('baz', $map->at('baz'));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (qux) is out-of-bound.');

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

        $this->assertSame($modified, $map);

        $this->assertSame('foo', $map->at('foo'));
        $this->assertSame('bar', $map->at('bar'));
        $this->assertSame('baz', $map->at('baz'));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Key (qux) is out-of-bound.');

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

        $this->assertSame($modified, $map);

        $this->assertSame('foo', $map->at('foo'));
        $this->assertSame('bar', $map->at('bar'));
        $this->assertSame('baz', $map->at('baz'));
        $this->assertSame('qux', $map->at('qux'));
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

        $this->assertSame($modified, $map);

        $this->assertSame('foo', $map->at('foo'));
        $this->assertSame('bar', $map->at('bar'));
        $this->assertSame('baz', $map->at('baz'));
        $this->assertSame('qux', $map->at('qux'));
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

        $this->assertSame($modified, $map);
        $this->assertCount(1, $map);
        $this->assertSame('qux', $map->get('baz'));
        $this->assertNull($map->get('foo'));
        $this->assertNull($map->get('bar'));
    }
}
