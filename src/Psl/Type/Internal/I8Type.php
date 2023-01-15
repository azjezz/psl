<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Math;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;

/**
 * @ara-extends Type\Type<i8>
 *
 * @extends Type\Type<int<-128, 127>>
 *
 * @internal
 */
final class I8Type extends Type\Type
{
    /**
     * @ara-assert-if-true i8 $value
     *
     * @psalm-assert-if-true int<-128, 127> $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= Math\INT8_MIN && $value <= Math\INT8_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return i8
     *
     * @return int<-128, 127>
     */
    public function coerce(mixed $value): int
    {
        $integer = Type\int()
            ->withTrace($this->getTrace()->withFrame($this->toString()))
            ->coerce($value);

        if ($integer >= Math\INT8_MIN && $integer <= Math\INT8_MAX) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @ara-assert i8 $value
     *
     * @psalm-assert int<-128, 127> $value
     *
     * @throws AssertException
     *
     * @ara-return i8
     *
     * @return int<-128, 127>
     */
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= Math\INT8_MIN && $value <= Math\INT8_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'i8';
    }
}
