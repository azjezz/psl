<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option with none value.
 *
 * @template T
 *
 * @return Option<T>
 */
function none(): Option
{
    return Option::none();
}
