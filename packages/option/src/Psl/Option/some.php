<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option with some value.
 *
 * @param T $value
 *
 * @return Option<T>
 *
 * @pure
 *
 * @api
 */
function some<T = mixed>(T $value): Option<T>
{
    return Option::some($value);
}
