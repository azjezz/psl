<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;

/**
 * @template-covariant T
 *
 * @implements TypeInterface<T>
 */
abstract readonly class Type implements TypeInterface
{
    /**
     * @psalm-assert-if-true T $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        try {
            $this->assert($value);

            return true;
        } catch (AssertException) {
            return false;
        }
    }

    #[\Override]
    public function isOptional(): bool
    {
        return false;
    }
}
