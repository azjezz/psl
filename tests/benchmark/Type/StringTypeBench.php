<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use Psl\Tests\Benchmark\Type\Asset\ExplicitStringableObject;
use Psl\Tests\Benchmark\Type\Asset\ImplicitStringableObject;

use function Psl\Type\string;

/** @extends GenericTypeBench<\Psl\Type\TypeInterface<string>> */
final class StringTypeBench extends GenericTypeBench
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
                    'type'  => string(),
                    'value' => 123,
                ],
                'instanceof Stringable (explicit)' => [
                    'type'  => string(),
                    'value' => new ImplicitStringableObject(),
                ],
                'instanceof Stringable (implicit)' => [
                    'type'  => string(),
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
     * @return array<non-empty-string, array{type: \Psl\Type\TypeInterface<string>, value: string}>
     */
    private function strictlyValidDataSet(): array
    {
        return [
            'string' => [
                'type'  => string(),
                'value' => 'foo',
            ],
        ];
    }
}
