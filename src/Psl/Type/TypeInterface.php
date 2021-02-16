<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Exception\TypeTrace;

/**
 * @template T
 */
interface TypeInterface
{
    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true T $value
     */
    public function matches($value): bool;

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @throws CoercionException
     */
    public function coerce($value);

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    public function assert($value);

    /**
     * Returns a string representation of the type.
     */
    public function toString(): string;

    /**
     * @return TypeInterface<T>
     */
    public function withTrace(TypeTrace $trace): TypeInterface;
}
