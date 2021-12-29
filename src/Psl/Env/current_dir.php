<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function getcwd;

/**
 * Returns the current working directory.
 *
 * @throws Psl\Exception\InvariantViolationException If unable to retrieve the current working directory.
 *
 * @return non-empty-string
 */
function current_dir(): string
{
    $directory = getcwd();
    Psl\invariant(false !== $directory, 'Unable to retrieve current working directory.');

    /** @var non-empty-string */
    return $directory;
}
