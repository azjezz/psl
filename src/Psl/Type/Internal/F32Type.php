<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Math;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function Psl\Type;

/**
 * @ara-extends Type\Type<f32>
 *
 * @extends Type\Type<float>
 *
 * @internal
 */
final readonly class F32Type extends Type\Type
{
    /**
     * @ara-assert-if-true f32 $value
     *
     * @psalm-assert-if-true float $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        return is_float($value) && $value >= MATH\FLOAT32_MIN && $value <= MATH\FLOAT32_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return f32
     *
     * @return float $value
     */
    #[\Override]
    public function coerce(mixed $value): float
    {
        $float = Type\float()->coerce($value);

        if ($float >= MATH\FLOAT32_MIN && $float <= MATH\FLOAT32_MAX) {
            return $float;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @ara-assert f32 $value
     *
     * @psalm-assert float $value
     *
     * @throws AssertException
     *
     * @ara-return f32
     *
     * @return float
     */
    #[\Override]
    public function assert(mixed $value): float
    {
        if (is_float($value) && $value >= MATH\FLOAT32_MIN && $value <= MATH\FLOAT32_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return 'f32';
    }
}
