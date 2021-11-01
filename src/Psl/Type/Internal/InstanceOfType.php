<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;

/**
 * @template T as object
 *
 * @extends Type<T>
 *
 * @internal
 */
final class InstanceOfType extends Type
{
    /**
     * @var class-string<T> $classname
     */
    private string $classname;

    /**
     * @param class-string<T> $classname
     */
    public function __construct(
        string $classname
    ) {
        $this->classname = $classname;
    }

    /**
     * @psalm-assert-if-true T $value
     */
    public function matches(mixed $value): bool
    {
        return $value instanceof $this->classname;
    }

    /**
     * @throws CoercionException
     *
     * @return T
     */
    public function coerce(mixed $value): object
    {
        if ($value instanceof $this->classname) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return T
     *
     * @psalm-assert T $value
     */
    public function assert(mixed $value): object
    {
        if ($value instanceof $this->classname) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return $this->classname;
    }
}
