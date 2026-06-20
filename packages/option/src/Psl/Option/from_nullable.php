<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option from a mixed value (Some) or null (None).
 *
 * @param null|T $value
 *
 * @return Option<T>
 *
 * @pure
 *
 * @api
 */
function from_nullable<T>(null|T $value): Option<T>
{
    return null !== $value ? Option::some($value) : Option::none();
}
