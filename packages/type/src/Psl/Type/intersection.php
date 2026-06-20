<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @api
 */
function intersection<TFirst: object, TSecond: object>(TypeInterface<TFirst> $first, TypeInterface<TSecond> $second): TypeInterface<TFirst&TSecond>
{
    return new Internal\IntersectionType::<TFirst, TSecond>($first, $second);
}
