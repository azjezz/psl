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
 * @ara-extends Type\Type<i32>
 *
 * @extends Type\Type<int<-2147483648, 2147483647>>
 *
 * @internal
 */
final class I32Type extends Type\Type
{
    /**
     * @ara-assert-if-true i32 $value
     *
     * @psalm-assert-if-true int<-2147483648, 2147483647> $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= Math\INT32_MIN && $value <= MATH\INT32_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return i32
     *
     * @return int<-2147483648, 2147483647>
     */
    public function coerce(mixed $value): int
    {
        $integer = Type\int()
            ->withTrace($this->getTrace()->withFrame($this->toString()))
            ->coerce($value);

        if ($integer >= Math\INT32_MIN && $integer <= MATH\INT32_MAX) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @ara-assert i32 $value
     *
     * @psalm-assert int<-2147483648, 2147483647> $value
     *
     * @throws AssertException
     *
     * @ara-return i32
     *
     * @return int<-2147483648, 2147483647>
     */
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= Math\INT32_MIN && $value <= MATH\INT32_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'i32';
    }
}
