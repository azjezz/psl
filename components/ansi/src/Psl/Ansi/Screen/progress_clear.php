<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;

/**
 * Remove the terminal progress indicator (OSC 9;4;0).
 *
 * Supported by Windows Terminal, ConEmu, Kitty, and Ghostty.
 *
 * @pure
 */
function progress_clear(): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::Notify, '4;0');
}
