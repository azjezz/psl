<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Stringable;

use function is_int;
use function is_string;

/**
 * @extends Type\Type<string>
 *
 * @internal
 */
final class StringType extends Type\Type
{
    /**
     * @psalm-assert-if-true string $value
     */
    public function matches(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * @throws CoercionException
     */
    public function coerce(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-assert string $value
     *
     * @throws AssertException
     */
    public function assert(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'string';
    }
}
