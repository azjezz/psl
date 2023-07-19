<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Type;

/**
 * @extends Type<mixed>
 *
 * @internal
 */
final class MixedType extends Type
{
    /**
     * @psalm-assert-if-true mixed $value
     */
    public function matches(mixed $value): bool
    {
        return true;
    }

    /**
     * @psalm-assert mixed $value
     */
    public function coerce(mixed $value): mixed
    {
        return $value;
    }

    /**
     * @psalm-assert mixed $value
     */
    public function assert(mixed $value): mixed
    {
        return $value;
    }

    public function toString(): string
    {
        return 'mixed';
    }
}
