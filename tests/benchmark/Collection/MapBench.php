<?php

namespace Psl\Tests\Benchmark\Collection;

use PhpBench\Attributes\Groups;
use Psl\Collection\MapInterface;
use Psl\Collection\Map;

class MapBench extends AbstractMapBench
{
    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $items
     *
     * @return MapInterface<TKey, TValue>
     */
    protected function createFromIterable(iterable $items): MapInterface
    {
        return Map::fromItems($items);
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey, TValue> $items
     *
     * @return MapInterface<TKey, TValue>
     */
    protected function createFromArray(array $items): MapInterface
    {
        return Map::fromArray($items);
    }
}
