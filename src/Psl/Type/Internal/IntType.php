<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * @extends Type<int>
 *
 * @internal
 */
final class IntType extends Type
{
    /**
     * @psalm-param mixed $value
     *
     * @psalm-return int
     *
     * @throws TypeCoercionException
     */
    public function coerce($value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (Str\is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
            $str = (string)$value;
            $int = Str\to_int($str);
            if (null !== $int) {
                return $int;
            }

            $trimmed = Str\trim_left($str, '0');
            $int = Str\to_int($trimmed);
            if (null !== $int) {
                return $int;
            }

            // Exceptional case "000" -(trim)-> "", but we want to return 0
            if ('' === $trimmed && '' !== $str) {
                return 0;
            }
        }

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return int
     *
     * @throws TypeAssertException
     */
    public function assert($value): int
    {
        if (is_int($value)) {
            return $value;
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'int';
    }
}
