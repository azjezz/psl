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
final readonly class NullType extends Type\Type<null>
{
    /**
     * @psalm-assert-if-true null $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return null === $value;
    }

    /**
     * @return null
     */
    #[Override]
    public function coerce(mixed $value): null
    {
        if (null === $value) {
            return null;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert null $value
     *
     * @return null
     */
    #[Override]
    public function assert(mixed $value): null
    {
        if (null === $value) {
            return null;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'null';
    }
}
