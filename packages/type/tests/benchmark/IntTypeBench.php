<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Benchmark;

use Override;
use PhpBench\Attributes\Groups;
use Psl\Type;
use Psl\Type\Tests\Benchmark\Asset\ExplicitStringableObject;
use Psl\Type\Tests\Benchmark\Asset\ImplicitStringableObject;

use function array_merge;

/**
 * @extends GenericTypeBench<Type\TypeInterface<int>>
 */
#[Groups(['type'])]
final class IntTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    #[Override]
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
    #[Override]
    public function provideHappyPathAssertion(): array
    {
        return $this->strictlyValidDataSet();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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
