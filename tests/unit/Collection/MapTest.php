<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection\Exception;
use Psl\Collection\Map;
use Psl\Collection\Vector;

final class MapTest extends AbstractMapTest
{
    /**
     * @var class-string<Map>
     */
    protected string $mapClass = Map::class;

    /**
     * @var class-string<Vector>
     */
    protected string $vectorClass = Vector::class;

    public function testFromItems(): void
    {
        $map = Map::fromItems([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        static::assertSame('bar', $map->at('foo'));
        static::assertSame('baz', $map->at('bar'));
        static::assertSame('qux', $map->at('baz'));
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

        $this->expectException(Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Key (124) was out-of-bounds.');

        $map[124];
    }

    public function testOffsetSetThrows(): void
    {
        $map = $this->create([
            'foo' => '1',
            'bar' => '2',
            'baz' => '3',
        ]);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot use object of type Psl\Collection\Map as array');

        $map['foo'] = 'qux';
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

    public function testOffsetUnsetThrows(): void
    {
        $map = $this->create([
            'foo' => '1',
            'bar' => '2',
            'baz' => '3',
        ]);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot use object of type Psl\Collection\Map as array');

        unset($map['foo']);
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

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @param iterable<Tk, Tv> $items
     *
     * @return Map<Tk, Tv>
     */
    #[\Override]
    protected function create(iterable $items): Map
    {
        return Map::fromArray($items);
    }
}
