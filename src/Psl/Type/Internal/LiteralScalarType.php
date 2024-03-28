<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template T of string|int|float|bool
 *
 * @extends Type\Type<T>
 *
 * @internal
 */
final class LiteralScalarType extends Type\Type
{
    /**
     * @param T $value
     */
    public function __construct(
        private string|int|float|bool $value
    ) {
    }

    /**
     * @psalm-assert-if-true T $value
     */
    public function matches(mixed $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @throws CoercionException
     *
     * @return T
     */
    public function coerce(mixed $value): string|int|float|bool
    {
        if ($value === $this->value) {
            /** @var T $value */
            return $value;
        }

        if (Type\string()->matches($this->value)) {
            $coerced_value = Type\string()->coerce($value);
            if ($this->value === $coerced_value) {
                /** @var T $coerced_value */
                return $coerced_value;
            }

            throw CoercionException::withValue($value, $this->toString());
        }

        if (Type\int()->matches($this->value)) {
            $coerced_value = Type\int()->coerce($value);
            if ($this->value === $coerced_value) {
                /** @var T $coerced_value */
                return $coerced_value;
            }

            throw CoercionException::withValue($value, $this->toString());
        }

        if (Type\float()->matches($this->value)) {
            $coerced_value = Type\float()->coerce($value);
            if ($this->value === $coerced_value) {
                /** @var T $coerced_value */
                return $coerced_value;
            }

            throw CoercionException::withValue($value, $this->toString());
        }

        /** @var bool $literal_value */
        $literal_value = $this->value;
        $coerced_value = Type\bool()->coerce($value);
        if ($literal_value === $coerced_value) {
            /** @var T $coerced_value */
            return $coerced_value;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    public function assert(mixed $value): string|int|float|bool
    {
        if ($this->value === $value) {
            /** @var T */
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    public function toString(): string
    {
        /** @var int|string|float|bool $value */
        $value = $this->value;
        if (Type\string()->matches($value)) {
            return Str\format('"%s"', $value);
        }

        if (Type\int()->matches($value)) {
            return Str\format('%d', $value);
        }

        if (Type\float()->matches($value)) {
            /** @psalm-suppress MissingThrowsDocblock */
            $string_representation = Str\trim_right(Str\format('%.14F', $value), '0');
            /** @psalm-suppress MissingThrowsDocblock */
            if (Str\ends_with($string_representation, '.')) {
                $string_representation .= '0';
            }

            return $string_representation;
        }

        return $value ? 'true' : 'false';
    }
}
