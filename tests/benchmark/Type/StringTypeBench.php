<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use PhpBench\Attributes\Groups;
use Psl\Tests\Benchmark\Type\Asset\ExplicitStringableObject;
use Psl\Tests\Benchmark\Type\Asset\ImplicitStringableObject;
use Psl\Type;

/**
 * @extends GenericTypeBench<Type\TypeInterface<string>>
 */
#[Groups(['type'])]
final class StringTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    public function provideHappyPathCoercion(): array
    {
        return array_merge($this->strictlyValidDataSet(), [
            'int' => [
                'type' => Type\string(),
                'value' => 123,
            ],
            'instanceof Stringable (explicit)' => [
                'type' => Type\string(),
                'value' => new ImplicitStringableObject(),
            ],
            'instanceof Stringable (implicit)' => [
                'type' => Type\string(),
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
     * @return array<non-empty-string, array{type: Type\TypeInterface<string>, value: string}>
     */
    private function strictlyValidDataSet(): array
    {
        return ['string' => [
            'type' => Type\string(),
            'value' => 'foo',
        ]];
    }
}
