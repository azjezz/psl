<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option with some value.
 *
 * @template T
 *
 * @param T $value
 *
 * @return Option<T>
 */
function some(mixed $value): Option
{
    return Option::some($value);
}
