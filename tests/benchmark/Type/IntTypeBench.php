<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use PhpBench\Attributes\Groups;
use Psl\Tests\Benchmark\Type\Asset\ExplicitStringableObject;
use Psl\Tests\Benchmark\Type\Asset\ImplicitStringableObject;
use Psl\Type;

/**
 * @extends GenericTypeBench<Type\TypeInterface<int>>
 */
#[Groups(['type'])]
final class IntTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    public function provideHappyPathCoercion(): array
    {
        return array_merge($this->strictlyValidDataSet(), [
            'string' => [
                'type' => Type\int(),
                'value' => '123',
            ],
            'float' => [
                'type' => Type\int(),
                'value' => 123.0,
            ],
            'instanceof Stringable (explicit)' => [
                'type' => Type\int(),
                'value' => new ImplicitStringableObject(),
            ],
            'instanceof Stringable (implicit)' => [
                'type' => Type\int(),
                'value' => new ExplicitStringableObject(),
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function provideHappyPathAssertion(): array
    {
        return $this->strictlyValidDataSet();
    }

    /**
     * {@inheritDoc}
     */
    public function provideHappyPathMatches(): array
    {
        return $this->strictlyValidDataSet();
    }

    /**
     * @return array<non-empty-string, array{type: Type\TypeInterface<int>, value: int}>
     */
    private function strictlyValidDataSet(): array
    {
        return ['int' => [
            'type' => Type\int(),
            'value' => 123,
        ]];
    }
}
