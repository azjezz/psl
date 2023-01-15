<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Stringable;

use function is_float;
use function is_int;
use function is_string;
use function ltrim;

/**
 * @ara-extends Type\Type<uint>
 *
 * @extends Type\Type<int<0, max>>
 *
 * @internal
 */
final class UIntType extends Type\Type
{
    /**
     * @ara-assert-if-true unit $value
     *
     * @psalm-assert-if-true int<0, max> $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= 0;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return uint
     *
     * @return int<0, max>
     */
    public function coerce(mixed $value): int
    {
        if (is_int($value) && $value >= 0) {
            return $value;
        } elseif (is_float($value)) {
            $integer_value = (int) $value;
            if (((float) $integer_value) === $value && $integer_value >= 0) {
                return $integer_value;
            }
        } elseif (is_string($value) || $value instanceof Stringable) {
            $str = (string)$value;
            $int = (int)$str;
            if ($str === (string) $int && $int >= 0) {
                return $int;
            }

            $trimmed = ltrim($str, '0');
            $int     = (int) $trimmed;
            if ($trimmed === (string) $int && $int >= 0) {
                return $int;
            }

            // Exceptional case "000" -(trim)-> "", but we want to return 0
            if ('' === $trimmed && '' !== $str) {
                return 0;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @ara-assert uint $value
     *
     * @psalm-assert int<0, max> $value
     *
     * @throws AssertException
     */
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= 0) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'uint';
    }
}
