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
 * @ara-extends Type\Type<u8>
 *
 * @extends Type\Type<int<0, 255>>
 *
 * @internal
 */
final class U8Type extends Type\Type
{
    /**
     * @ara-assert-if-true u8 $value
     *
     * @psalm-assert-if-true int<0, 255> $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= 0 && $value <= MATH\UINT8_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return u8
     *
     * @return int<0, 255>
     */
    public function coerce(mixed $value): int
    {
        $integer = Type\int()
            ->withTrace($this->getTrace()->withFrame($this->toString()))
            ->coerce($value);

        if ($integer >= 0 && $integer <= MATH\UINT8_MAX) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @ara-assert u8 $value
     *
     * @psalm-assert int<0, 255> $value
     *
     * @throws AssertException
     *
     * @ara-return u8
     *
     * @return int<0, 255>
     */
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= 0 && $value <= MATH\UINT8_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'u8';
    }
}
