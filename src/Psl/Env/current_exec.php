<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl\Filesystem;

/**
 * Returns the full filesystem path of the current running executable.
 */
function current_exec(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $canonicalScriptName = Filesystem\canonicalize($scriptName);
    $executable = $canonicalScriptName ?? $scriptName;

    // @codeCoverageIgnoreStart
    if (Filesystem\is_symbolic_link($executable)) {
        $executable = Filesystem\read_symbolic_link($executable);
    }

    // @codeCoverageIgnoreEnd

    return $executable;
}
