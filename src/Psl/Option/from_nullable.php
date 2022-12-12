<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option from a mixed value (Some) or null (None).
 *
 * @template T
 *
 * @param null|T $value
 *
 * @return Option<T>
 */
function from_nullable(mixed $value): Option
{
    return $value !== null ? Option::some($value) : Option::none();
}
