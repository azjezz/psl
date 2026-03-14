<?php

declare(strict_types=1);

namespace Psl\Async\Internal;

use function str_increment;

/**
 * Generate a unique sequential string identifier.
 *
 * @internal
 *
 * @codeCoverageIgnore
 *
 * @return non-empty-string
 */
function next_id(): string
{
    static $id = 'a';

    $current = $id;
    $id = str_increment($id);

    return $current;
}
