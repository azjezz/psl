<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @internal
 */
final readonly class OptionalType<T> extends Type\Type<T>
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(
        private Type\TypeInterface<T> $inner,
    ) {}

    /**
     * @throws CoercionException
     */
    #[Override]
    public function coerce(mixed $value): T
    {
        return $this->inner->coerce($value);
    }

    /**
     * @throws AssertException
     *
     * @psalm-assert T $value
     */
    #[Override]
    public function assert(mixed $value): T
    {
        return $this->inner->assert($value);
    }

    /**
     * Return whether this type is optional.
     */
    #[Override]
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * Returns a string representation of the type.
     */
    #[Override]
    public function toString(): string
    {
        return $this->inner->toString();
    }
}
