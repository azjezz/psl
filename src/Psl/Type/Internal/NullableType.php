<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl;
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
     * @var Type\TypeInterface<T>
     */
    private Type\TypeInterface $inner;

    /**
     * @param Type\TypeInterface<T> $inner
     *
     * @throws Psl\Exception\InvariantViolationException If $inner is optional.
     */
    public function __construct(
        Type\TypeInterface $inner
    ) {
        Psl\invariant(!$inner->isOptional(), 'Optional type must be the outermost.');

        $this->inner = $inner;
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true T|null $value
     */
    public function matches($value): bool
    {
        return null === $value || $this->inner->matches($value);
    }

    /**
     * @param mixed $value
     *
     * @throws CoercionException
     *
     * @return T|null
     */
    public function coerce($value)
    {
        if (null === $value) {
            return null;
        }

        return $this->inner->withTrace($this->getTrace())->coerce($value);
    }

    /**
     * @param mixed $value
     *
     * @throws AssertException
     *
     * @return T|null
     *
     * @psalm-assert T|null $value
     */
    public function assert($value)
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
