<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Exception\TypeTrace;

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
    public function coerce(mixed $value);

    /**
     * @throws AssertException
     *
     * @return T
     *
     * @psalm-assert T $value
     */
    public function assert(mixed $value);

    /**
     * Return whether this type is optional.
     */
    public function isOptional(): bool;

    /**
     * Returns a string representation of the type.
     */
    public function toString(): string;

    /**
     * @return TypeInterface<T>
     */
    public function withTrace(TypeTrace $trace): TypeInterface;
}
