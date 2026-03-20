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
 * @extends GenericTypeBench<Type\TypeInterface<array-key>>
 */
#[Groups(['type'])]
final class ArrayKeyTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    #[Override]
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
