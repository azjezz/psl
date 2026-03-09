<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;

/**
 * Set the terminal progress indicator state and value (OSC 9;4).
 *
 * Supported by Windows Terminal, ConEmu, Kitty, and Ghostty.
 *
 * The progress value is a percentage (0-100) and is used for {@see ProgressState::Normal},
 * {@see ProgressState::Error}, and {@see ProgressState::Warning} states.
 *
 * For {@see ProgressState::Indeterminate}, the progress value is ignored.
 *
 * @param int<0, 100> $progress Percentage value (0-100).
 *
 * @pure
 */
function progress(ProgressState $state, int $progress = 0): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::Notify, '4;' . $state->value . ';' . $progress);
}
