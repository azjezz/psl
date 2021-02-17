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
     * @param mixed $value
     *
     * @psalm-assert-if-true bool $value
     */
    public function matches($value): bool
    {
        return is_bool($value);
    }

    /**
     * @throws CoercionException
     */
    public function coerce($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (0 === $value) {
            return false;
        }

        if (1 === $value) {
            return true;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-assert bool $value
     *
     * @throws AssertException
     */
    public function assert($value): bool
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
