<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @psalm-param Type<T> $spec
 *
 * @psalm-return Type<T|null>
 */
function nullable(Type $spec): Type
{
    return new Internal\UnionType($spec, null());
}
