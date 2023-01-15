<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @ara-extends Type\Type<nonnull>
 *
 * @extends Type\Type<mixed>
 *
 * @internal
 */
final class NonNullType extends Type\Type
{
    /**
     * @psalm-assert-if-true mixed $value
     *
     * @ara-assert-if-true nonnull $value
     */
    public function matches(mixed $value): bool
    {
        return null !== $value;
    }

    /**
     * @ara-return nonnull
     *
     * @return mixed
     */
    public function coerce(mixed $value): mixed
    {
        if (null !== $value) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @ara-assert nonnull $value
     *
     * @psalm-assert mixed $value
     *
     * @ara-return nonnull
     *
     * @return mixed
     */
    public function assert(mixed $value): mixed
    {
        if (null !== $value) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'nonnull';
    }
}
