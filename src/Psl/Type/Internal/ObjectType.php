<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;

use function is_object;

/**
 * @extends Type<object>
 *
 * @internal
 */
final readonly class ObjectType extends Type
{
    /**
     * @psalm-assert-if-true T $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        return is_object($value);
    }

    /**
     * @throws CoercionException
     *
     * @return object
     */
    #[\Override]
    public function coerce(mixed $value): object
    {
        if (is_object($value)) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return object
     *
     * @psalm-assert object $value
     */
    #[\Override]
    public function assert(mixed $value): object
    {
        if (is_object($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return 'object';
    }
}
