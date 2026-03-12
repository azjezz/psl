<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template T
 *
 * @extends Type\Type<T|null>
 *
 * @internal
 */
final readonly class NullishType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<T> $inner
     */
    public function __construct(
        private Type\TypeInterface $inner,
    ) {}

    /**
     * @psalm-assert-if-true T|null $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return null === $value || $this->inner->matches($value);
    }

    /**
     * @throws CoercionException
     *
     * @return T|null
     */
    #[Override]
    public function coerce(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return $this->inner->coerce($value);
    }

    /**
     * @throws AssertException
     *
     * @return T|null
     *
     * @psalm-assert T|null $value
     */
    #[Override]
    public function assert(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

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
        return '?' . $this->inner->toString();
    }
}
