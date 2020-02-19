<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Exception;
use Psl\Iter;

class ImmMapTest extends TestCase
{
    public function testItems(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        $items = $map->items();

        self::assertSame(2, $items->count());

        self::assertSame('foo', $items->at(0)->first());
        self::assertSame(1, $items->at(0)->last());

        self::assertSame('bar', $items->at(1)->first());
        self::assertSame(2, $items->at(1)->last());
    }

    public function testIsEmpty(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        self::assertFalse($map->isEmpty());

        $map = new Collection\ImmMap([]);

        self::assertTrue($map->isEmpty());
    }

    public function testCount(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);
        self::assertCount(2, $map);
    }

    public function testToArray(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);
        $array = $map->toArray();

        self::assertArrayHasKey('foo', $array);
        self::assertArrayHasKey('bar', $array);

        self::assertSame(1, $array['foo']);
        self::assertSame(2, $array['bar']);
    }

    public function testAt(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        self::assertSame(1, $map->at('foo'));
        self::assertSame(2, $map->at('bar'));

        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Key (baz) is out-of-bound.');

        $map->at('baz');
    }

    public function testContainsKey(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => null]);

        self::assertTrue($map->containsKey('foo'));
        self::assertTrue($map->containsKey('bar'));
        self::assertTrue($map->containsKey('baz'));
        self::assertFalse($map->containsKey('qux'));
    }

    public function testGet(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2]);

        self::assertSame(1, $map->get('foo'));
        self::assertSame(2, $map->get('bar'));
        self::assertNull($map->get('baz'));
    }

    public function testContains(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => null]);

        self::assertTrue($map->contains('foo'));
        self::assertTrue($map->contains('bar'));
        self::assertTrue($map->contains('baz'));
        self::assertFalse($map->contains('qux'));
    }

    public function testGetIterator(): void
    {
        $map = new Collection\ImmMap(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4]);

        $iterator = $map->getIterator();
        self::assertInstanceOf(Iter\Iterator::class, $iterator);

        $array = \iterator_to_array($iterator);
        self::assertSame(['foo' => 1, 'bar' => 2, 'baz' => 3, 'qux' => 4], $array);
    }
}
