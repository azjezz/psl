<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;
use PhpBench\Attributes\Groups;
use Psl\Type;
use Psl\Vec;

/**
 * @extends GenericTypeBench<Type\TypeInterface<list<mixed>>>
 */
#[Groups(['type'])]
final class VecTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function provideHappyPathCoercion(): array
    {
        $arraysAndIterables = [];

        foreach ($this->arrayDataSet() as $key => $pair) {
            $arraysAndIterables[$key . ' array'] = $pair;
            $arraysAndIterables[$key . ' iterable'] = [
                'type' => $pair['type'],
                'value' => new ArrayIterator($pair['value']),
            ];
        }

        return $arraysAndIterables;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function provideHappyPathAssertion(): array
    {
        $arraysAndIterables = [];

        foreach ($this->arrayDataSet() as $key => $pair) {
            $arraysAndIterables[$key . ' array'] = $pair;
        }

        return $arraysAndIterables;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function provideHappyPathMatches(): array
    {
        return $this->provideHappyPathAssertion();
    }

    /**
     * @return array<non-empty-string, array{type: Type\TypeInterface<list<mixed>>, value: array}>
     *
     * @psalm-suppress MissingThrowsDocblock this block should never throw
     */
    private function arrayDataSet(): array
    {
        return [
            'mixed, empty' => [
                'type' => Type\vec(Type\mixed()),
                'value' => [],
            ],
            'mixed, non-empty' => [
                'type' => Type\vec(Type\mixed()),
                'value' => [
                    'foo',
                    'bar',
                    'baz',
                ],
            ],
            'mixed, large' => [
                'type' => Type\vec(Type\mixed()),
                'value' => Vec\range(0, 100),
            ],
            'int, empty' => [
                'type' => Type\vec(Type\int()),
                'value' => [],
            ],
            'int, non-empty' => [
                'type' => Type\vec(Type\int()),
                'value' => [
                    4,
                    25,
                    8,
                ],
            ],
        ];
    }
}
