<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;

/**
 * @template Tl
 * @template Tr
 *
 * @param TypeInterface<Tl> $first
 * @param TypeInterface<Tr> $second
 *
 * @throws Psl\Exception\InvariantViolationException If $first, or $second is optional.
 *
 * @return TypeInterface<Tl|Tr>
 */
function union(
    TypeInterface $first,
    TypeInterface $second
): TypeInterface {
    return new Internal\UnionType($first, $second);
}
