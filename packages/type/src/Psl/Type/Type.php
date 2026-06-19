<?php

declare(strict_types=1);

namespace Psl\Type;

use Override;
use Psl\Type\Exception\AssertException;

/**
 * @api
 */
abstract readonly class Type<T = mixed> implements TypeInterface<T>
{
    /**
     * @psalm-assert-if-true T $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        try {
            $this->assert($value);

            return true;
        } catch (AssertException) {
            return false;
        }
    }

    #[Override]
    public function isOptional(): bool
    {
        return false;
    }
}
