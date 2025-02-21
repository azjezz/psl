<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use BackedEnum;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template T of BackedEnum
 *
 * @extends Type\Type<T>
 */
final readonly class BackedEnumType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param class-string<T> $enum
     */
    public function __construct(
        private readonly string $enum,
    ) {
    }

    #[\Override]
    public function matches(mixed $value): bool
    {
        return $value instanceof $this->enum;
    }

    /**
     * @throws CoercionException
     *
     * @return T
     */
    #[\Override]
    public function coerce(mixed $value): BackedEnum
    {
        if ($value instanceof $this->enum) {
            return $value;
        }

        foreach ($this->enum::cases() as $case) {
            if (Type\string()->matches($case->value)) {
                $string_value = Type\string()->coerce($value);

                if ($string_value === $case->value) {
                    return $case;
                }

                continue;
            }

            $integer_value = Type\int()->coerce($value);

            if ($integer_value === $case->value) {
                return $case;
            }
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return T
     *
     * @psalm-assert T $value
     */
    #[\Override]
    public function assert(mixed $value): BackedEnum
    {
        if ($value instanceof $this->enum) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return Str\format('backed-enum(%s)', $this->enum);
    }
}
