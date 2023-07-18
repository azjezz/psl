<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_float;
use function is_int;
use function is_object;
use function is_string;

/**
 * @extends Type\Type<positive-int>
 *
 * @internal
 */
final class PositiveIntType extends Type\Type
{
    /**
     * @psalm-assert-if-true positive-int $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value > 0;
    }

    /**
     * @throws CoercionException
     *
     * @return positive-int
     */
    public function coerce(mixed $value): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
            $str = (string)$value;
            $int = Str\to_int($str);
            if (null !== $int && $int > 0) {
                return $int;
            }

            try {
                $trimmed = Str\trim_left($str, '0');
            } catch (Str\Exception\InvalidArgumentException $e) {
                throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
            }

            $int = Str\to_int($trimmed);
            if (null !== $int && $int > 0) {
                return $int;
            }

            // Exceptional case "000" -(trim)-> "", but we treat it as 0
            if ('' === $trimmed && '' !== $str) {
                CoercionException::withValue($value, $this->toString(), $this->getTrace());
            }
        }

        if (is_float($value)) {
            $integer_value = (int) $value;
            $reconstructed = (float) $integer_value;
            if ($reconstructed === $value && $integer_value > 0) {
                return $integer_value;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-assert positive-int $value
     *
     * @throws AssertException
     *
     * @return positive-int
     */
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'positive-int';
    }
}
