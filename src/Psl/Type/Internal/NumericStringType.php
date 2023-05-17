<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Stringable;

use function is_numeric;
use function is_string;

/**
 * @extends Type\Type<numeric-string>
 *
 * @internal
 */
final class NumericStringType extends Type\Type
{
    /**
     * @psalm-assert-if-true numeric-string $value
     */
    public function matches(mixed $value): bool
    {
        return is_string($value) && is_numeric($value);
    }

    /**
     * @throws CoercionException
     *
     * @return numeric-string
     */
    public function coerce(mixed $value): string
    {
        if (is_string($value) && is_numeric($value)) {
            /** @var numeric-string $value */
            return $value;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if ($value instanceof Stringable) {
            $str = (string) $value;
            if (is_numeric($str)) {
                return $str;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return numeric-string
     *
     * @psalm-assert numeric-string $value
     */
    public function assert(mixed $value): string
    {
        if (is_string($value) && is_numeric($value)) {
            /** @var numeric-string $value */
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'numeric-string';
    }
}
