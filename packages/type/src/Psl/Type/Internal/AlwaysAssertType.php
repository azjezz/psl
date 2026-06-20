<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @internal
 */
final readonly class AlwaysAssertType<T> extends Type\Type<T>
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(
        private Type\TypeInterface<T> $inner,
    ) {}

    /**
     * @throws CoercionException
     */
    #[Override]
    public function coerce(mixed $value): T
    {
        if ($this->inner->matches($value)) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    #[Override]
    public function assert(mixed $value): T
    {
        return $this->inner->assert($value);
    }

    #[Override]
    public function toString(): string
    {
        return $this->inner->toString();
    }
}
