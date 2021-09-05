<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use Psl\Tests\Benchmark\Type\Asset\ExplicitStringableObject;
use Psl\Tests\Benchmark\Type\Asset\ImplicitStringableObject;
use function Psl\Type\int;

/** @psalm-extends GenericTypeBench<\Psl\Type\Internal\IntType> */
final class IntTypeBench extends GenericTypeBench
{
    /** {@inheritDoc} */
    public function provideHappyPathCoercion(): array
    {
        return array_merge(
            $this->strictlyValidDataSet(),
            [
                'string' => [
                    'type'  => int(),
                    'value' => '123',
                ],
                'float' => [
                    'type'  => int(),
                    'value' => 123.0,
                ],
                'instanceof Stringable (explicit)' => [
                    'type'  => int(),
                    'value' => new ImplicitStringableObject(),
                ],
                'instanceof Stringable (implicit)' => [
                    'type'  => int(),
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
            'int'    => [
                'type'  => int(),
                'value' => 123,
            ],
        ];
    }
}
