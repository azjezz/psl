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
 * @extends Type\Type<int>
 *
 * @internal
 */
final class IntType extends Type\Type
{
    /**
     * @psalm-assert-if-true int $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value);
    }

    /**
     * @throws CoercionException
     */
    public function coerce(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            $integer_value = (int) $value;
            if (((float) $integer_value) === $value) {
                return $integer_value;
            }
        }

        if (is_string($value) || $value instanceof Stringable) {
            $str = (string)$value;
            $int = (int)$str;
            if ($str === (string) $int) {
                return $int;
            }

            $trimmed = ltrim($str, '0');
            $int     = (int) $trimmed;
            if ($trimmed === (string) $int) {
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
     * @psalm-assert int $value
     *
     * @throws AssertException
     */
    public function assert(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'int';
    }
}
