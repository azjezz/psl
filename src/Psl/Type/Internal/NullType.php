<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;

/**
 * @extends Type\Type<null>
 *
 * @internal
 */
final class NullType extends Type\Type
{
    /**
     * @psalm-param mixed $value
     *
     * @psalm-return null
     */
    public function coerce($value)
    {
        if (Type\is_null($value)) {
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
        if (Type\is_null($value)) {
            return null;
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'null';
    }
}
