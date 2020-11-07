<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @template T
 *
 * @psalm-param Type<T> $value_type
 *
 * @psalm-return Type<Collection\MutableVectorInterface<T>>
 */
function mutable_vector(Type $value_type): Type
{
    return new Internal\MutableVectorType($value_type);
}
