<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * @template T as object
 *
 * @extends Type<T>
 *
 * @internal
 */
final class ObjectType extends Type
{
    /**
     * @psalm-var class-string<T> $classname
     */
    private string $classname;

    /**
     * @psalm-param class-string<T> $classname
     */
    public function __construct(
        string $classname
    ) {
        $this->classname = $classname;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @throws TypeCoercionException
     */
    public function coerce($value): object
    {
        if ($value instanceof $this->classname) {
            return $value;
        }

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @throws TypeAssertException
     */
    public function assert($value): object
    {
        if ($value instanceof $this->classname) {
            return $value;
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return $this->classname;
    }
}
