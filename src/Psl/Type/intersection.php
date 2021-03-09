<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tl
 * @template Tr
 *
 * @param TypeInterface<Tl> $left_type
 * @param TypeInterface<Tr> $right_type
 *
 * @return TypeInterface<Tl&Tr>
 */
function intersection(
    TypeInterface $left_type,
    TypeInterface $right_type
): TypeInterface {
    /** @var TypeInterface<Tl&Tr> */
    return new Internal\IntersectionType($left_type, $right_type);
}
