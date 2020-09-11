<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @extends Type\Type<int>
 *
 * @internal
 */
final class IntType extends Type\Type
{
    /**
     * @psalm-param mixed $value
     *
     * @psalm-return int
     *
     * @throws CoercionException
     */
    public function coerce($value): int
    {
        if (Type\is_int($value)) {
            return $value;
        }

        if (Type\is_string($value) || (Type\is_object($value) && method_exists($value, '__toString'))) {
            $str = (string)$value;
            $int = Str\to_int($str);
            if (null !== $int) {
                return $int;
            }

            $trimmed = Str\trim_left($str, '0');
            $int     = Str\to_int($trimmed);
            if (null !== $int) {
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
     * @psalm-param mixed $value
     *
     * @psalm-return int
     *
     * @psalm-assert int $value
     *
     * @throws AssertException
     */
    public function assert($value): int
    {
        if (Type\is_int($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'int';
    }
}
