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
final readonly class ArrayKeyType extends UnionType
{
    /**
     * @psalm-mutation-free
     */
    public function __construct()
    {
        /** @psalm-suppress MissingThrowsDocblock */
        parent::__construct(new StringType(), new IntType());
    }

    #[\Override]
    public function matches(mixed $value): bool
    {
        return is_string($value) || is_int($value);
    }

    #[\Override]
    public function assert(mixed $value): mixed
    {
        // happy path performance optimization:
        if (is_string($value) || is_int($value)) {
            return $value;
        }

        return parent::assert($value);
    }

    #[\Override]
    public function coerce(mixed $value): mixed
    {
        // happy path performance optimization:
        if (is_string($value) || is_int($value)) {
            return $value;
        }

        return parent::coerce($value);
    }

    #[\Override]
    public function toString(): string
    {
        return 'array-key';
    }
}
