<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

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
final class NullableType extends Type\Type
{
    /**
     * @param Type\TypeInterface<T> $inner
     */
    public function __construct(
        private readonly Type\TypeInterface $inner
    ) {
    }

    /**
     * @psalm-assert-if-true T|null $value
     */
    public function matches(mixed $value): bool
    {
        return null === $value || $this->inner->matches($value);
    }

    /**
     * @throws CoercionException
     *
     * @return T|null
     */
    public function coerce(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return $this->inner->withTrace($this->getTrace())->coerce($value);
    }

    /**
     * @throws AssertException
     *
     * @return T|null
     *
     * @psalm-assert T|null $value
     */
    public function assert(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return $this->inner->withTrace($this->getTrace())->assert($value);
    }

    public function toString(): string
    {
        return '?' . $this->inner->toString();
    }
}
