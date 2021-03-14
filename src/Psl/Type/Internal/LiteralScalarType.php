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
     * @var T
     */
    private $value;

    /**
     * @param T $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true T $value
     */
    public function matches($value): bool
    {
        return $this->value === $value;
    }

    /**
     * @param mixed $value
     *
     * @return T
     *
     * @throws CoercionException
     */
    public function coerce($value)
    {
        if ($value === $this->value) {
            /** @var T $value */
            return $value;
        }

        /** @psalm-suppress DocblockTypeContradiction */
        if (Type\string()->matches($this->value)) {
            $coerced_value = Type\string()->withTrace($this->getTrace())->coerce($value);
            if ($this->value === $coerced_value) {
                /** @var T $coerced_value */
                return $coerced_value;
            }

            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        /** @psalm-suppress DocblockTypeContradiction */
        if (Type\int()->matches($this->value)) {
            $coerced_value = Type\int()->withTrace($this->getTrace())->coerce($value);
            if ($this->value === $coerced_value) {
                /** @var T $coerced_value */
                return $coerced_value;
            }

            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        /** @psalm-suppress DocblockTypeContradiction */
        if (Type\float()->matches($this->value)) {
            $coerced_value = Type\float()->withTrace($this->getTrace())->coerce($value);
            if ($this->value === $coerced_value) {
                /** @var T $coerced_value */
                return $coerced_value;
            }

            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        /** @var bool $literal_value */
        $literal_value = $this->value;
        $coerced_value = Type\bool()->withTrace($this->getTrace())->coerce($value);
        if ($literal_value === $coerced_value) {
            /**
             * @psalm-suppress InvalidReturnStatement
             * 
             * @var T $coerced_value
             */
            return $coerced_value;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    public function assert($value)
    {
        if ($this->value === $value) {
            /** @var T */
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
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
