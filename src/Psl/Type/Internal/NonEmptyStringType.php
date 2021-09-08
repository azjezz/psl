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
 * @extends Type\Type<non-empty-string>
 *
 * @internal
 */
final class NonEmptyStringType extends Type\Type
{
    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    public function matches(mixed $value): bool
    {
        return '' !== $value
            && is_string($value);
    }

    /**
     * @throws CoercionException
     *
     * @return non-empty-string
     */
    public function coerce(mixed $value): string
    {
        if ('' !== $value && is_string($value)) {
            /** @var non-empty-string $value */
            return $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if ($value instanceof Stringable) {
            $str = (string) $value;
            if ('' !== $str) {
                return $str;
            }
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return non-empty-string
     *
     * @psalm-assert non-empty-string $value
     */
    public function assert(mixed $value): string
    {
        if ('' !== $value && is_string($value)) {
            /** @var non-empty-string $value */
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'non-empty-string';
    }
}
