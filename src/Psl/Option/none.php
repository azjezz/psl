<?php

declare(strict_types=1);

namespace Psl\Option;

/**
 * Create an option with none value.
 *
 * @return Option<never>
 */
function none(): Option
{
    return Option::none();
}
