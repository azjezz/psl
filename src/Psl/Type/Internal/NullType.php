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
     * @psalm-assert-if-true null $value
     */
    public function matches(mixed $value): bool
    {
        return null === $value;
    }

    /**
     * @return null
     */
    public function coerce(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-assert null $value
     *
     * @return null
     */
    public function assert(mixed $value): mixed
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
