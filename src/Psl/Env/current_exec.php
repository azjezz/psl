<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;
use Psl\Iter;

/**
 * Returns the full filesystem path of the current running executable.
 *
 * @throws Psl\Exception\InvariantViolationException If unable to retrieve the current running executable.
 */
function current_exec(): string
{
    $files = get_included_files();
    $executable = Iter\first($files);
    Psl\invariant(null !== $executable, 'Unable to retrieve the full filesystem path of the current running executable.');

    return $executable;
}
