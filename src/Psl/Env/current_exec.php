<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl\Filesystem;

/**
 * Returns the full filesystem path of the current running executable.
 */
function current_exec(): string
{
    $script_name = $_SERVER['SCRIPT_NAME'];
    $canonical_script_name = Filesystem\canonicalize($script_name);
    $executable = $canonical_script_name ?? $script_name;

    // @codeCoverageIgnoreStart
    if (Filesystem\is_symbolic_link($executable)) {
        $executable = Filesystem\read_symbolic_link($executable);
    }

    // @codeCoverageIgnoreEnd

    return $executable;
}
