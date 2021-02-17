<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function ctype_digit;
use function is_float;
use function is_int;
use function is_object;
use function is_string;

/**
 * @extends Type\Type<float>
 *
 * @internal
 */
final class FloatType extends Type\Type
{
    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true float $value
     */
    public function matches($value): bool
    {
        return is_float($value);
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return float
     *
     * @throws CoercionException
     */
    public function coerce($value): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
            $str = (string) $value;
            if ('' === $str) {
                throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
            }

            if (ctype_digit($str)) {
                return (float)$str;
            }

            if (1 === preg_match("/^-?(?:\\d*\\.)?\\d+(?:[eE]\\d+)?$/", $str)) {
                return (float)$str;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return float
     *
     * @psalm-assert float $value
     *
     * @throws AssertException
     */
    public function assert($value): float
    {
        if (is_float($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'float';
    }
}
