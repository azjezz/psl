<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * @extends Type<null>
 *
 * @internal
 */
final class NullType extends Type
{
    /**
     * @psalm-param mixed $value
     *
     * @psalm-return null
     */
    public function coerce($value)
    {
        if (null === $value) {
            return null;
        }

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
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

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'null';
    }
}
