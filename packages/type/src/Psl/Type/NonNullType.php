<?php

declare(strict_types=1);

namespace Psl\Type;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * This type is not marked as internal, cause the class is being leaked by the nonnull() function.
 * This is necessary to get coerce and assert narrow down the type without psalm having a TNonNull type.
 *
 * @api
 */
final readonly class NonNullType extends Type\Type<mixed>
{
    /**
     * @psalm-assert-if-true mixed $value
     *
     * @return ($value is null ? false : true)
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return null !== $value;
    }

    /**
     * @return ($value is null ? never : mixed)
     */
    #[Override]
    public function coerce(mixed $value): mixed
    {
        if (null !== $value) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert mixed $value
     *
     * @return ($value is null ? never : mixed)
     */
    #[Override]
    public function assert(mixed $value): mixed
    {
        if (null !== $value) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'nonnull';
    }
}
