<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;
use function is_object;
use function is_string;

/**
 * @extends Type\Type<int>
 *
 * @internal
 */
final class IntType extends Type\Type
{
    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true int $value
     */
    public function matches($value): bool
    {
        return is_int($value);
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return int
     *
     * @throws CoercionException
     */
    public function coerce($value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
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
