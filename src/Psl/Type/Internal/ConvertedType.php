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
final readonly class ConvertedType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param TypeInterface<I> $from
     * @param TypeInterface<O> $into
     * @param (Closure(I): O) $converter
     */
    public function __construct(
        private TypeInterface $from,
        private TypeInterface $into,
        private Closure $converter,
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @ara-return O
     *
     * @return O
     */
    #[\Override]
    public function coerce(mixed $value): mixed
    {
        if ($this->into->matches($value)) {
            return $value;
        }

        $action = 0;

        try {
            $coercedInput = $this->from->coerce($value);
            $action++;
            $converted = ($this->converter)($coercedInput);
            $action++;
            return $this->into->coerce($converted);
        } catch (Throwable $failure) {
            throw CoercionException::withValue(
                $value,
                match ($action) {
                    0 => $this->from->toString(),
                    default => $this->into->toString(),
                },
                match ($action) {
                    0 => PathExpression::coerceInput($value, $this->from->toString()),
                    1 => PathExpression::convert($coercedInput ?? null, $this->into->toString()),
                    default => PathExpression::coerceOutput($converted ?? null, $this->into->toString()),
                },
                $failure,
            );
        }
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
    #[\Override]
    public function assert(mixed $value): mixed
    {
        return $this->into->assert($value);
    }

    #[\Override]
    public function toString(): string
    {
        return $this->into->toString();
    }
}
