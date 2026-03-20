<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_float;
use function Psl\Type;

/**
 * @extends Type\Type<float>
 *
 * @internal
 */
final readonly class F32Type extends Type\Type
{
    /**
     * @psalm-assert-if-true float $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return is_float($value) && $value >= -3.402_823_47E+38 && $value <= 3.402_823_47E+38;
    }

    /**
     * @throws CoercionException
     *
     * @return float $value
     */
    #[Override]
    public function coerce(mixed $value): float
    {
        $float = Type\float()->coerce($value);

        if ($float >= -3.402_823_47E+38 && $float <= 3.402_823_47E+38) {
            return $float;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert float $value
     *
     * @throws AssertException
     *
     * @return float
     */
    #[Override]
    public function assert(mixed $value): float
    {
        if (is_float($value) && $value >= -3.402_823_47E+38 && $value <= 3.402_823_47E+38) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'f32';
    }
}
