<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function rtrim;
use function sprintf;
use function str_ends_with;

/**
 * @template T of string|int|float|bool
 *
 * @extends Type\Type<T>
 *
 * @internal
 */
final readonly class LiteralScalarType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param T $value
     */
    public function __construct(
        private string|int|float|bool $value,
    ) {}

    /**
     * @psalm-assert-if-true T $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @throws CoercionException
     *
     * @return T
     */
    #[Override]
    public function coerce(mixed $value): string|int|float|bool
    {
        $expectedScalarValue = $this->value;
        if ($value === $expectedScalarValue) {
            /** @var T $value */
            return $value;
        }

        $stringType = Type\string();
        if ($stringType->matches($this->value)) {
            $coercedValue = $stringType->coerce($value);
            if ($expectedScalarValue === $coercedValue) {
                /** @var T $coercedValue */
                return $coercedValue;
            }

            throw CoercionException::withValue($value, $this->toString());
        }

        $intType = Type\int();
        if ($intType->matches($this->value)) {
            $coercedValue = $intType->coerce($value);
            if ($expectedScalarValue === $coercedValue) {
                /** @var T $coercedValue */
                return $coercedValue;
            }

            throw CoercionException::withValue($value, $this->toString());
        }

        $floatType = Type\float();
        if ($floatType->matches($this->value)) {
            $coercedValue = $floatType->coerce($value);
            if ($expectedScalarValue === $coercedValue) {
                /** @var T $coercedValue */
                return $coercedValue;
            }

            throw CoercionException::withValue($value, $this->toString());
        }

        /** @var bool $literalValue */
        $literalValue = $expectedScalarValue;
        $coercedValue = Type\bool()->coerce($value);
        if ($literalValue === $coercedValue) {
            /** @var T $coercedValue */
            return $coercedValue;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    #[Override]
    public function assert(mixed $value): string|int|float|bool
    {
        if ($this->value === $value) {
            /** @var T */
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        /** @var int|string|float|bool $value */
        $value = $this->value;
        if (Type\string()->matches($value)) {
            return sprintf('"%s"', $value);
        }

        if (Type\int()->matches($value)) {
            return sprintf('%d', $value);
        }

        if (Type\float()->matches($value)) {
            $stringRepresentation = rtrim(sprintf('%.14F', $value), '0');
            if (str_ends_with($stringRepresentation, '.')) {
                $stringRepresentation .= '0';
            }

            return $stringRepresentation;
        }

        return $value ? 'true' : 'false';
    }
}
