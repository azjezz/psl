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
final class OptionalType extends Type\Type
{
    /**
     * @var Type\TypeInterface<T>
     */
    private Type\TypeInterface $inner;

    /**
     * @param Type\TypeInterface<T> $inner
     */
    public function __construct(
        Type\TypeInterface $inner
    ) {
        $this->inner = $inner;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @throws CoercionException
     */
    public function coerce($value)
    {
        return $this->inner->withTrace($this->getTrace())->coerce($value);
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    public function assert($value)
    {
        return $this->inner->withTrace($this->getTrace())->assert($value);
    }

    /**
     * Return whether this type is optional.
     */
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * Returns a string representation of the type.
     */
    public function toString(): string
    {
        return $this->inner->toString();
    }
}
