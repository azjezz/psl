<?php

namespace Psl\Tests\Benchmark\Collection;

use PhpBench\Attributes\Groups;
use Psl\Collection\MutableMapInterface;
use Psl\Collection\MutableMap;

class MutableMapBench extends AbstractMapBench
{
    public function benchmarkSet(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        foreach (\range('a', 'k') as $value) {
            $map->set($value, $value);
        }
    }

    public function benchmarkSetAll(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        $map->setAll([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
            'f' => 'f',
            'g' => 'g',
            'h' => 'h',
            'i' => 'i',
            'j' => 'j',
            'k' => 'k',
        ]);
    }
    public function benchmarkAdd(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        foreach (\range('A', 'Z') as $value) {
            $map->add($value, $value);
        }
    }
    public function benchmarkAddAll(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        // TODO
        $map->addAll([
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'H' => 'H',
            'I' => 'I',
            'J' => 'J',
            'K' => 'K',
        ]);
    }
    public function benchmarkRemove(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        foreach (\range('a', 'k') as $value) {
            $map->remove($value);
        }
    }
    public function benchmarkClear(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        $map->clear();
    }

    protected function createFromIterable(iterable $items): MutableMapInterface
    {
        return MutableMap::fromItems($items);
    }

    protected function createFromArray(array $items): MutableMapInterface
    {
        return MutableMap::fromArray($items);
    }
}
