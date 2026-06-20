<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Closure;
use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\TypeInterface;
use Throwable;

/**
 * @internal
 */
final readonly class ConvertedType<I, O> extends Type\Type<O>
{
    /**
     * @psalm-mutation-free
     *
     * @param (Closure(I): O) $converter
     */
    public function __construct(
        private TypeInterface<I> $from,
        private TypeInterface<O> $into,
        private Closure $converter,
    ) {}

    /**
     * @throws CoercionException
     */
    #[Override]
    public function coerce(mixed $value): O
    {
        if ($this->into->matches($value)) {
            return $value;
        }

        /** @var int */
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
     * @psalm-assert O $value
     *
     * @throws AssertException
     */
    #[Override]
    public function assert(mixed $value): O
    {
        return $this->into->assert($value);
    }

    #[Override]
    public function toString(): string
    {
        return $this->into->toString();
    }
}
