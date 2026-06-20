<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option from a mixed value (Some) or null (None).
 *
 * @pure
 *
 * @api
 */
function from_nullable<T>(null|T $value): Option<T>
{
    return null !== $value ? Option::<mixed>::some($value) : Option::none();
}
