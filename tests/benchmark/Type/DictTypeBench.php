<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;
use PhpBench\Attributes\Groups;
use Psl\Dict;
use Psl\Type;
use Psl\Vec;

/**
 * @extends GenericTypeBench<Type\TypeInterface<array>>
 */
#[Groups(['type'])]
final class DictTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
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
    public function provideHappyPathMatches(): array
    {
        return $this->provideHappyPathAssertion();
    }

    /**
     * @return array<non-empty-string, array{type: Type\TypeInterface<array>, value: array}>
     *
     * @psalm-suppress MissingThrowsDocblock this method should never throw
     */
    private function arrayDataSet(): array
    {
        return [
            'generic array, empty' => [
                'type' => Type\dict(Type\array_key(), Type\mixed()),
                'value' => [],
            ],
            'generic array, non-empty' => [
                'type' => Type\dict(Type\array_key(), Type\mixed()),
                'value' => ['foo' => 'bar'],
            ],
            'generic array, large' => [
                'type' => Type\dict(Type\array_key(), Type\mixed()),
                'value' => Vec\fill(100, null),
            ],
            'int array, empty' => [
                'type' => Type\dict(Type\int(), Type\mixed()),
                'value' => [],
            ],
            'int array, non-empty' => [
                'type' => Type\dict(Type\int(), Type\mixed()),
                'value' => [
                    'foo',
                    'bar',
                    'baz',
                ],
            ],
            'int array, large' => [
                'type' => Type\dict(Type\int(), Type\mixed()),
                'value' => Vec\fill(100, null),
            ],
            'map, empty' => [
                'type' => Type\dict(Type\string(), Type\mixed()),
                'value' => [],
            ],
            'map, non-empty' => [
                'type' => Type\dict(Type\string(), Type\mixed()),
                'value' => [
                    'foo' => 'bar',
                    'baz' => 'tab',
                    'taz' => 'tar',
                ],
            ],
            'map, large' => [
                'type' => Type\dict(Type\string(), Type\mixed()),
                'value' => Dict\associate(
                    Vec\map(Vec\range(0, 99), static fn(int $key): string => 'key' . ((string) $key)),
                    Vec\fill(100, null),
                ),
            ],
        ];
    }
}
