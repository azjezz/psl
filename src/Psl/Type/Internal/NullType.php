<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @extends Type\Type<null>
 *
 * @internal
 */
final class NullType extends Type\Type
{
    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true null $value
     */
    public function matches($value): bool
    {
        return null === $value;
    }

    /**
     * @param mixed $value
     *
     * @return null
     */
    public function coerce($value)
    {
        if (null === $value) {
            return null;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert null $value
     *
     * @return null
     */
    public function assert($value)
    {
        if (null === $value) {
            return null;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'null';
    }
}
