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
     * @param mixed $value
     *
     * @psalm-assert-if-true mixed $value
     */
    public function matches($value): bool
    {
        return true;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function coerce($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert mixed $value
     *
     * @return mixed
     */
    public function assert($value)
    {
        return $value;
    }

    public function toString(): string
    {
        return 'mixed';
    }
}
