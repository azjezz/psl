<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function is_link;
use function readlink;
use function realpath;

/**
 * Returns the full filesystem path of the current running executable.
 *
 * @throws Psl\Exception\InvariantViolationException If unable to retrieve the current running executable.
 */
function current_exec(): string
{
    $executable = realpath((string) $_SERVER['SCRIPT_NAME']);
    // @codeCoverageIgnoreStart
    if (is_link($executable)) {
        /** @var string $executable */
        $executable = readlink($executable);
    }
    // @codeCoverageIgnoreEnd

    return $executable;
}
