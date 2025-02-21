<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template-covariant T
 */
interface TypeInterface
{
    /**
     * @psalm-assert-if-true T $value
     */
    public function matches(mixed $value): bool;

    /**
     * @throws CoercionException
     *
     * @return T
     */
    public function coerce(mixed $value): mixed;

    /**
     * @throws AssertException
     *
     * @return T
     *
     * @psalm-assert T $value
     */
    public function assert(mixed $value): mixed;

    /**
     * Return whether this type is optional.
     */
    public function isOptional(): bool;

    /**
     * Returns a string representation of the type.
     */
    public function toString(): string;
}
