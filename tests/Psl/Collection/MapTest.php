<?php

declare(strict_types=1);

namespace Psl\Tests\Collection;

use Psl\Collection\Map;
use Psl\Collection\Vector;

/**
 * @covers \Psl\Collection\Map
 */
final class MapTest extends AbstractMapTest
{
    /**
     * @psalm-var class-string<Map>
     */
    protected string $mapClass = Map::class;

    /**
     * @psalm-var class-string<Vector>
     */
    protected string $vectorClass = Vector::class;

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @psalm-param  iterable<Tk, Tv> $items
     *
     * @psalm-return Map<Tk, Tv>
     */
    protected function create(iterable $items): Map
    {
        return new Map($items);
    }
}
