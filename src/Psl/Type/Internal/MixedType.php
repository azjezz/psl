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
     * @psalm-param mixed $value
     *
     * @psalm-return mixed
     */
    public function coerce($value)
    {
        return $value;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-assert mixed $value
     *
     * @psalm-return mixed
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
