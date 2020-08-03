<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @psalm-param Type<T> $spec
 *
 * @psalm-return Type<null|T>
 */
function nullable(Type $spec): Type
{
    return new Internal\UnionType(null(), $spec);
}
