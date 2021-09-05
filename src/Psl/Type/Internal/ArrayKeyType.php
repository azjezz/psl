<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use function is_int;
use function is_string;

/**
 * @extends UnionType<string, int>
 *
 * @internal
 */
final class ArrayKeyType extends UnionType
{
    public function __construct()
    {
        /** @psalm-suppress MissingThrowsDocblock */
        parent::__construct(new StringType(), new IntType());
    }

    public function matches(mixed $value): bool
    {
        return is_string($value) || is_int($value);
    }

    public function assert(mixed $value): mixed
    {
        // happy path performance optimization:
        if (is_string($value) || is_int($value)) {
            return $value;
        }

        return parent::assert($value);
    }

    public function coerce(mixed $value): mixed
    {
        // happy path performance optimization:
        if (is_string($value) || is_int($value)) {
            return $value;
        }

        return parent::coerce($value);
    }

    public function toString(): string
    {
        return 'array-key';
    }
}
