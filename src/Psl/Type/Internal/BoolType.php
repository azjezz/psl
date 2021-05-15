<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_bool;

/**
 * @extends Type\Type<bool>
 *
 * @internal
 */
final class BoolType extends Type\Type
{
    /**
     * @psalm-assert-if-true bool $value
     */
    public function matches(mixed $value): bool
    {
        return is_bool($value);
    }

    /**
     * @throws CoercionException
     */
    public function coerce(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (0 === $value || '0' === $value) {
            return false;
        }

        if (1 === $value || '1' === $value) {
            return true;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-assert bool $value
     *
     * @throws AssertException
     */
    public function assert(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'bool';
    }
}
