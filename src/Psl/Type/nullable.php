<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @psalm-param TypeInterface<T> $spec
 *
 * @psalm-return TypeInterface<T|null>
 */
function nullable(TypeInterface $spec): TypeInterface
{
    return new Internal\UnionType($spec, null());
}
