<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl\Filesystem;

/**
 * Returns the full filesystem path of the current running executable.
 */
function current_exec(): string
{
    /** @var non-empty-string $executable */
    $executable = (string) Filesystem\canonicalize($_SERVER['SCRIPT_NAME'] ?? '');
    // @codeCoverageIgnoreStart
    if (Filesystem\is_symbolic_link($executable)) {
        /** @psalm-suppress MissingThrowsDocblock */
        $executable = Filesystem\read_symbolic_link($executable);
    }
    // @codeCoverageIgnoreEnd

    return $executable;
}
