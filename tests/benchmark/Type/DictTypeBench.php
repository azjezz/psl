<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;

use function array_combine;
use function array_fill;
use function array_map;
use function Psl\Type\array_key;
use function Psl\Type\dict;
use function Psl\Type\int;
use function Psl\Type\mixed;
use function Psl\Type\optional;
use function Psl\Type\shape;
use function Psl\Type\string;

/** @psalm-extends GenericTypeBench<\Psl\Type\Internal\DictType> */
final class DictTypeBench extends GenericTypeBench
{
    /** {@inheritDoc} */
    public function provideHappyPathCoercion(): array
    {
        $arraysAndIterables = [];

        foreach ($this->arrayDataSet() as $key => $pair) {
            $arraysAndIterables[$key . ' array'] = $pair;
            $arraysAndIterables[$key . ' iterable'] = [
                'type' => $pair['type'],
                'value' => new ArrayIterator($pair['value'])
            ];
        }

        return $arraysAndIterables;
    }

    /** {@inheritDoc} */
    public function provideHappyPathAssertion(): array
    {
        $arraysAndIterables = [];

        foreach ($this->arrayDataSet() as $key => $pair) {
            $arraysAndIterables[$key . ' array'] = $pair;
        }

        return $arraysAndIterables;
    }

    /** {@inheritDoc} */
    public function provideHappyPathMatches(): array
    {
        return $this->provideHappyPathAssertion();
    }

    /** @return array<non-empty-string, array{type: \Psl\Type\Internal\DictType, value: array}> */
    private function arrayDataSet(): array
    {
        return [
            'generic array, empty' => [
                'type' => dict(array_key(), mixed()),
                'value' => [],
            ],
            'generic array, non-empty' => [
                'type' => dict(array_key(), mixed()),
                'value' => ['foo' => 'bar'],
            ],
            'generic array, large' => [
                'type' => dict(array_key(), mixed()),
                'value' => array_fill(0, 100, null),
            ],
            'int array, empty' => [
                'type' => dict(int(), mixed()),
                'value' => [],
            ],
            'int array, non-empty' => [
                'type' => dict(int(), mixed()),
                'value' => [
                    'foo',
                    'bar',
                    'baz',
                ],
            ],
            'int array, large' => [
                'type' => dict(int(), mixed()),
                'value' => array_fill(0, 100, null),
            ],
            'map, empty' => [
                'type' => dict(string(), mixed()),
                'value' => [],
            ],
            'map, non-empty' => [
                'type' => dict(string(), mixed()),
                'value' => [
                    'foo' => 'bar',
                    'baz' => 'tab',
                    'taz' => 'tar',
                ],
            ],
            'map, large' => [
                'type' => dict(string(), mixed()),
                'value' => array_combine(
                    array_map(static fn (int $key): string => 'key' . $key, range(0, 99)),
                    array_fill(0, 100, null),
                ),
            ],
        ];
    }
}
