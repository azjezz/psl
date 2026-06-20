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
final readonly class NullableType<T> extends Type\Type<T|null>
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(
        private Type\TypeInterface<T> $inner,
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
     */
    #[Override]
    public function coerce(mixed $value): T|null
    {
        if (null === $value) {
            return null;
        }

        return $this->inner->coerce($value);
    }

    /**
     * @throws AssertException
     *
     * @psalm-assert T|null $value
     */
    #[Override]
    public function assert(mixed $value): T|null
    {
        if (null === $value) {
            return null;
        }

        return $this->inner->assert($value);
    }

    #[Override]
    public function toString(): string
    {
        return '?' . $this->inner->toString();
    }
}
