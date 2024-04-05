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
final readonly class NonNullType extends Type\Type
{
    /**
     * @template T of mixed
     *
     * @param T|null $value
     *
     * @psalm-assert-if-true T $value
     *
     * @ara-assert-if-true nonnull $value
     *
     * @return ($value is null ? false : true)
     */
    public function matches(mixed $value): bool
    {
        return null !== $value;
    }

    /**
     * @template T of mixed
     *
     * @param T|null $value
     *
     * @ara-return nonnull
     *
     * @return ($value is null ? never : T)
     */
    public function coerce(mixed $value): mixed
    {
        if (null !== $value) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @template T
     *
     * @param T|null $value
     *
     * @ara-assert nonnull $value
     *
     * @psalm-assert T $value
     *
     * @ara-return nonnull
     *
     * @return ($value is null ? never : T)
     */
    public function assert(mixed $value): mixed
    {
        if (null !== $value) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    public function toString(): string
    {
        return 'nonnull';
    }
}
