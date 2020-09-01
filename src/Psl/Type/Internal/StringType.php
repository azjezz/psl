<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;

/**
 * @extends Type\Type<string>
 *
 * @internal
 */
final class StringType extends Type\Type
{
    /**
     * @psalm-param mixed $value
     *
     * @psalm-return string
     *
     * @throws TypeCoercionException
     */
    public function coerce($value): string
    {
        if (Type\is_string($value)) {
            return $value;
        }

        if (Type\is_int($value)) {
            return (string)$value;
        }

        if (Type\is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return string
     *
     * @psalm-assert string $value
     *
     * @throws TypeAssertException
     */
    public function assert($value): string
    {
        if (Type\is_string($value)) {
            return $value;
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'string';
    }
}
