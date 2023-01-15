<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Math;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;

/**
 * @ara-extends Type\Type<i16>
 *
 * @extends Type\Type<int<-32768, 32767>>
 *
 * @internal
 */
final class I16Type extends Type\Type
{
    /**
     * @ara-assert-if-true i16 $value
     *
     * @psalm-assert-if-true int<-32768, 32767> $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= Math\INT16_MIN && $value <= MATH\INT16_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return i16
     *
     * @return int<-32768, 32767>
     */
    public function coerce(mixed $value): int
    {
        $integer = Type\int()
            ->withTrace($this->getTrace()->withFrame($this->toString()))
            ->coerce($value);

        if ($integer >= Math\INT16_MIN && $integer <= MATH\INT16_MAX) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @ara-assert i16 $value
     *
     * @psalm-assert int<-32768, 32767> $value
     *
     * @throws AssertException
     *
     * @ara-return i16
     *
     * @return int<-32768, 32767>
     */
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= Math\INT16_MIN && $value <= MATH\INT16_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'i16';
    }
}
