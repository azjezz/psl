<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Math;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;
use function Psl\Type;

/**
 * @ara-extends Type\Type<u16>
 *
 * @extends Type\Type<int<0, 65535>>
 *
 * @internal
 */
final class U16Type extends Type\Type
{
    /**
     * @ara-assert-if-true u16 $value
     *
     * @psalm-assert-if-true int<0, 65535> $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= 0 && $value <= MATH\UINT16_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return u16
     *
     * @return int<0, 65535>
     */
    public function coerce(mixed $value): int
    {
        $integer = Type\int()
            ->withTrace($this->getTrace()->withFrame($this->toString()))
            ->coerce($value);

        if ($integer >= 0 && $integer <= MATH\UINT16_MAX) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @ara-assert u16 $value
     *
     * @psalm-assert int<0, 65535> $value
     *
     * @throws AssertException
     *
     * @ara-return u16
     *
     * @return int<0, 65535>
     */
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= 0 && $value <= MATH\UINT16_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'u16';
    }
}
