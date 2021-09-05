<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use Psl\Tests\Benchmark\Type\Asset\ExplicitStringableObject;
use Psl\Tests\Benchmark\Type\Asset\ImplicitStringableObject;
use function Psl\Type\string;

/** @psalm-extends GenericTypeBench<\Psl\Type\Internal\NonEmptyStringType> */
final class NonEmptyStringTypeBench extends GenericTypeBench
{
    /** {@inheritDoc} */
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

    /** {@inheritDoc} */
    public function provideHappyPathAssertion(): array
    {
        return $this->strictlyValidDataSet();
    }

    /** {@inheritDoc} */
    public function provideHappyPathMatches(): array
    {
        return $this->strictlyValidDataSet();
    }

    /** @return array<non-empty-string, array{type: \Psl\Type\Internal\DictType, value: array}> */
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
