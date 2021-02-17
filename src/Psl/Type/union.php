<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tl
 * @template Tr
 *
 * @psalm-param TypeInterface<Tl> $left_type
 * @psalm-param TypeInterface<Tr> $right_type
 *
 * @psalm-return TypeInterface<Tl|Tr>
 */
function union(
    TypeInterface $left_type,
    TypeInterface $right_type
): TypeInterface {
    return new Internal\UnionType($left_type, $right_type);
}
