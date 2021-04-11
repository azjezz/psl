<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Collection;

use Psl\Collection\Map;
use Psl\Collection\Vector;

/**
 * @covers \Psl\Collection\Map
 */
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

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @param iterable<Tk, Tv> $items
     *
     * @return Map<Tk, Tv>
     */
    protected function create(iterable $items): Map
    {
        return new Map($items);
    }
}
