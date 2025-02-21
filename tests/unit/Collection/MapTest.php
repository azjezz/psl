<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

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
