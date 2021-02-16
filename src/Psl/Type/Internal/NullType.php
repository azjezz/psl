<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_null;

/**
 * @extends Type\Type<null>
 *
 * @internal
 */
final class NullType extends Type\Type
{
    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true null $value
     */
    public function matches($value): bool
    {
        return null === $value;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return null
     */
    public function coerce($value)
    {
        if (is_null($value)) {
            return null;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-assert null $value
     *
     * @psalm-return null
     */
    public function assert($value)
    {
        if (null === $value) {
            return null;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'null';
    }
}
