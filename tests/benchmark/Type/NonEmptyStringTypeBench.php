<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use Psl\Tests\Benchmark\Type\Asset\ExplicitStringableObject;
use Psl\Tests\Benchmark\Type\Asset\ImplicitStringableObject;
use Psl\Type;

/**
 * @extends GenericTypeBench<Type\TypeInterface<non-empty-string>>
 */
final class NonEmptyStringTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    public function provideHappyPathCoercion(): array
    {
        return array_merge(
            $this->strictlyValidDataSet(),
            [
                'int'    => [
                    'type'  => Type\non_empty_string(),
                    'value' => 123,
                ],
                'instanceof Stringable (explicit)' => [
                    'type'  => Type\non_empty_string(),
                    'value' => new ImplicitStringableObject(),
                ],
                'instanceof Stringable (implicit)' => [
                    'type'  => Type\non_empty_string(),
                    'value' => new ExplicitStringableObject(),
                ],
            ]
        );
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
     * @return array<non-empty-string, array{type: \Psl\Type\TypeInterface<non-empty-string>, value: non-empty-string}>
     */
    private function strictlyValidDataSet(): array
    {
        return [
            'string' => [
                'type'  => Type\non_empty_string(),
                'value' => 'foo',
            ],
        ];
    }
}
