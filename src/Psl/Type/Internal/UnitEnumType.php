<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use UnitEnum;

/**
 * @template T of UnitEnum
 *
 * @extends Type\Type<T>
 */
final class UnitEnumType extends Type\Type
{
    /**
     * @param class-string<T> $enum
     */
    public function __construct(
        private readonly string $enum
    ) {
    }

    public function matches(mixed $value): bool
    {
        return $value instanceof $this->enum;
    }

    /**
     * @throws CoercionException
     *
     * @return T
     */
    public function coerce(mixed $value): UnitEnum
    {
        if ($value instanceof $this->enum) {
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
    public function assert(mixed $value): UnitEnum
    {
        if ($value instanceof $this->enum) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('unit-enum(%s)', $this->enum);
    }
}
