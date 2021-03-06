<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\TypeTrace;

/**
 * @template T
 *
 * @implements TypeInterface<T>
 */
abstract class Type implements TypeInterface
{
    private ?TypeTrace $trace = null;

    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true T $value
     */
    public function matches($value): bool
    {
        try {
            $this->assert($value);

            return true;
        } catch (AssertException $_e) {
            return false;
        }
    }

    protected function getTrace(): TypeTrace
    {
        if (null === $this->trace) {
            $this->trace = new TypeTrace();
        }

        return $this->trace;
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
