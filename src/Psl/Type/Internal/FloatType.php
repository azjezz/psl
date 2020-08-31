<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use function ctype_digit;
use Psl\Type;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;

/**
 * @extends Type\Type<float>
 *
 * @internal
 */
final class FloatType extends Type\Type
{
    private const TYPE = 'float';

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return float
     *
     * @throws TypeCoercionException
     */
    public function coerce($value): float
    {
        if (Type\is_float($value)) {
            return $value;
        }

        if (Type\is_int($value)) {
            return $value;
        }

        if (Type\is_string($value) || (Type\is_object($value) && method_exists($value, '__toString'))) {
            $str = (string) $value;
            if ('' === $str) {
                throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
            }

            if (ctype_digit($str)) {
                return (float)$str;
            }

            if (1 === preg_match("/^-?(?:\\d*\\.)?\\d+(?:[eE]\\d+)?$/", $str)) {
                return (float)$str;
            }
        }

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return float
     *
     * @psalm-assert float $value
     *
     * @throws TypeAssertException
     */
    public function assert($value): float
    {
        if (Type\is_float($value)) {
            return $value;
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'float';
    }
}
