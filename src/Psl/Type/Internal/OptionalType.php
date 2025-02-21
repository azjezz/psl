<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template T
 *
 * @extends Type\Type<T>
 *
 * @internal
 */
final readonly class OptionalType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<T> $inner
     */
    public function __construct(
        private Type\TypeInterface $inner,
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @return T
     */
    #[\Override]
    public function coerce(mixed $value): mixed
    {
        return $this->inner->coerce($value);
    }

    /**
     * @throws AssertException
     *
     * @return T
     *
     * @psalm-assert T $value
     */
    #[\Override]
    public function assert(mixed $value): mixed
    {
        return $this->inner->assert($value);
    }

    /**
     * Return whether this type is optional.
     */
    #[\Override]
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * Returns a string representation of the type.
     */
    #[\Override]
    public function toString(): string
    {
        return $this->inner->toString();
    }
}
