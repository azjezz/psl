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
final readonly class StringType extends Type\Type
{
    /**
     * @psalm-assert-if-true string $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * @throws CoercionException
     */
    #[\Override]
    public function coerce(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert string $value
     *
     * @throws AssertException
     */
    #[\Override]
    public function assert(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return 'string';
    }
}
