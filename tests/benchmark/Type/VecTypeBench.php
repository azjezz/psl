<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;
use function Psl\Type\int;
use function Psl\Type\mixed;
use function Psl\Type\vec;
use function range;

/** @extends GenericTypeBench<\Psl\Type\TypeInterface<list<mixed>>> */
final class VecTypeBench extends GenericTypeBench
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

    /**
     * @return array<non-empty-string, array{type: \Psl\Type\TypeInterface<list<mixed>>, value: array}>
     *
     * @psalm-suppress MissingThrowsDocblock this block should never throw
     */
    private function arrayDataSet(): array
    {
        return [
            'mixed, empty' => [
                'type' => vec(mixed()),
                'value' => [],
            ],
            'mixed, non-empty' => [
                'type' => vec(mixed()),
                'value' => [
                    'foo',
                    'bar',
                    'baz',
                ],
            ],
            'mixed, large' => [
                'type' => vec(mixed()),
                'value' => range(0, 100),
            ],
            'int, empty' => [
                'type' => vec(int()),
                'value' => [],
            ],
            'int, non-empty' => [
                'type' => vec(int()),
                'value' => [
                    4,
                    25,
                    8,
                ],
            ],
        ];
    }
}
