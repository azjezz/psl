<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * @extends Type<bool>
 *
 * @internal
 */
final class BoolType extends Type
{
    /**
     * @psalm-return bool
     *
     * @throws TypeCoercionException
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

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-return bool
     *
     * @psalm-assert bool $value
     *
     * @throws TypeAssertException
     */
    public function assert($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'bool';
    }
}
