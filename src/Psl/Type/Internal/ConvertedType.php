<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Closure;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\TypeInterface;
use Throwable;

/**
 * @template I
 * @template O
 *
 * @ara-extends Type\Type<O>
 *
 * @extends Type\Type<O>
 *
 * @internal
 */
final class ConvertedType extends Type\Type
{
    /**
     * @param TypeInterface<I> $from
     * @param TypeInterface<O> $into
     * @param (Closure(I): O) $converter
     */
    public function __construct(
        private TypeInterface $from,
        private TypeInterface $into,
        private Closure $converter
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @ara-return O
     *
     * @return O
     */
    public function coerce(mixed $value): mixed
    {
        if ($this->into->matches($value)) {
            return $value;
        }

        $coercedInput = $this->from->coerce($value);

        try {
            $converted = ($this->converter)($coercedInput);
        } catch (Throwable $failure) {
            throw CoercionException::withConversionFailureOnValue($value, $this->toString(), $this->getTrace(), $failure);
        }

        return $this->into->coerce($converted);
    }

    /**
     * @ara-assert O $value
     *
     * @psalm-assert O $value
     *
     * @throws AssertException
     *
     * @ara-return O
     *
     * @return O
     */
    public function assert(mixed $value): mixed
    {
        return $this->into->assert($value);
    }

    public function toString(): string
    {
        return $this->into->toString();
    }
}
