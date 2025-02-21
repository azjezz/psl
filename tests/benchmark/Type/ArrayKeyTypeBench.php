<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use PhpBench\Attributes\Groups;
use Psl\Tests\Benchmark\Type\Asset\ExplicitStringableObject;
use Psl\Tests\Benchmark\Type\Asset\ImplicitStringableObject;
use Psl\Type;

/**
 * @extends GenericTypeBench<Type\TypeInterface<array-key>>
 */
#[Groups(['type'])]
final class ArrayKeyTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function provideHappyPathCoercion(): array
    {
        return array_merge($this->strictlyValidDataSet(), [
            'instanceof Stringable (explicit)' => [
                'type' => Type\array_key(),
                'value' => new ImplicitStringableObject(),
            ],
            'instanceof Stringable (implicit)' => [
                'type' => Type\array_key(),
                'value' => new ExplicitStringableObject(),
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function provideHappyPathAssertion(): array
    {
        return $this->strictlyValidDataSet();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function provideHappyPathMatches(): array
    {
        return $this->strictlyValidDataSet();
    }

    /**
     * @return array<non-empty-string, array{type: Type\TypeInterface<array-key>, value: array-key}>
     */
    private function strictlyValidDataSet(): array
    {
        return [
            'string' => [
                'type' => Type\array_key(),
                'value' => 'foo',
            ],
            'int' => [
                'type' => Type\array_key(),
                'value' => 123,
            ],
        ];
    }
}
