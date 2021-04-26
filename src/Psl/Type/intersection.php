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
 * @return TypeInterface<Tl&Tr>
 */
function intersection(
    TypeInterface $first,
    TypeInterface $second
): TypeInterface {
    /** @var TypeInterface<Tl&Tr> */
    return new Internal\IntersectionType($first, $second);
}
