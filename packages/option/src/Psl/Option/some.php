<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option with some value.
 *
 * @pure
 *
 * @api
 */
function some<T>(T $value): Option<T>
{
    return Option::<mixed>::some($value);
}
