<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @api
 */
interface TypeInterface<out T>
{
    /**
     * @psalm-assert-if-true T $value
     */
    public function matches(mixed $value): bool;

    /**
     * @throws CoercionException
     */
    public function coerce(mixed $value): T;

    /**
     * @throws AssertException
     *
     * @psalm-assert T $value
     */
    public function assert(mixed $value): T;

    /**
     * Return whether this type is optional.
     */
    public function isOptional(): bool;

    /**
     * Returns a string representation of the type.
     */
    public function toString(): string;
}
