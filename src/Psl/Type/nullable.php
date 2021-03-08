<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @param TypeInterface<T> $spec
 *
 * @return TypeInterface<T|null>
 */
function nullable(TypeInterface $spec): TypeInterface
{
    return new Internal\UnionType($spec, null());
}
