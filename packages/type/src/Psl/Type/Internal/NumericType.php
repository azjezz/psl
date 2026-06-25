<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;
use Stringable;

use function is_numeric;

/**
 * @extends Type<numeric>
 *
 * @internal
 */
final readonly class NumericType extends Type
{
    public function matches(mixed $value): bool
    {
        return is_numeric($value);
    }

    /** @return numeric */
    public function coerce(mixed $value): mixed
    {
        /** @var mixed $value */
        $value = $value instanceof Stringable ? (string) $value : $value;

        if (is_numeric($value)) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /** @return numeric */
    public function assert(mixed $value): mixed
    {
        if (is_numeric($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    public function toString(): string
    {
        return 'numeric';
    }
}
