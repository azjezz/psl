<?php

declare(strict_types=1);

namespace Psl\Env;

use function getcwd;
use Psl;

/**
 * Returns the current working directory
 *
 * @throws Psl\Exception\InvariantViolationException If unable to retrieve the current working directory.
 */
function current_dir(): string
{
    $directory = getcwd();
    Psl\invariant(false !== $directory, 'Unable to retrieve current working directory.');
    return $directory;
}
