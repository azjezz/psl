<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\TypeTrace;

/**
 * @template-covariant T
 *
 * @implements TypeInterface<T>
 */
abstract class Type implements TypeInterface
{
    private ?TypeTrace $trace = null;

    /**
     * @psalm-assert-if-true T $value
     */
    public function matches(mixed $value): bool
    {
        try {
            $this->assert($value);

            return true;
        } catch (AssertException) {
            return false;
        }
    }

    protected function getTrace(): TypeTrace
    {
        return $this->trace
            ?? $this->trace = new TypeTrace();
    }

    /**
     * @return TypeInterface<T>
     */
    public function withTrace(TypeTrace $trace): TypeInterface
    {
        $new        = clone $this;
        $new->trace = $trace;
        return $new;
    }

    public function isOptional(): bool
    {
        return false;
    }
}
