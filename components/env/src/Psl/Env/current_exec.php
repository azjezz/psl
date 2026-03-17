<?php

declare(strict_types=1);

namespace Psl\Env;

use function is_link;
use function readlink;
use function realpath;

/**
 * Returns the full filesystem path of the current running executable.
 */
function current_exec(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $canonicalScriptName = realpath($scriptName);
    $executable = false !== $canonicalScriptName ? $canonicalScriptName : $scriptName;

    // @codeCoverageIgnoreStart
    if (is_link($executable)) {
        $resolved = readlink($executable);
        $executable = $resolved === false ? $executable : $resolved;
    }

    // @codeCoverageIgnoreEnd

    return $executable;
}
