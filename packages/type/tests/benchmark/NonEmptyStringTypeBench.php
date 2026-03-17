<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Benchmark;

use Override;
use PhpBench\Attributes\Groups;
use Psl\Type;
use Psl\Type\Tests\Benchmark\Asset\ExplicitStringableObject;
use Psl\Type\Tests\Benchmark\Asset\ImplicitStringableObject;
use Psl\Type\TypeInterface;

/**
 * @extends GenericTypeBench<TypeInterface<non-empty-string>>
 */
#[Groups(['type'])]
final class NonEmptyStringTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function provideHappyPathCoercion(): array
    {
        return array_merge($this->strictlyValidDataSet(), [
            'int' => [
                'type' => Type\non_empty_string(),
                'value' => 123,
            ],
            'instanceof Stringable (explicit)' => [
                'type' => Type\non_empty_string(),
                'value' => new ImplicitStringableObject(),
            ],
            'instanceof Stringable (implicit)' => [
                'type' => Type\non_empty_string(),
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
     * @return array<non-empty-string, array{type: TypeInterface<non-empty-string>, value: non-empty-string}>
     */
    private function strictlyValidDataSet(): array
    {
        return ['string' => [
            'type' => Type\non_empty_string(),
            'value' => 'foo',
        ]];
    }
}
