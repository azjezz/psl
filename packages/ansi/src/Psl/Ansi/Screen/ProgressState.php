<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

/**
 * Progress indicator state for the terminal taskbar/tab.
 *
 * Used with the OSC 9;4 sequence, supported by Windows Terminal, ConEmu, Kitty, and Ghostty.
 */
enum ProgressState: int
{
    /**
     * Normal progress: displays a standard progress bar.
     */
    case Normal = 1;

    /**
     * Error: displays the progress bar in an error state (typically red).
     */
    case Error = 2;

    /**
     * Indeterminate: displays an animated progress indicator with no specific percentage.
     */
    case Indeterminate = 3;

    /**
     * Warning: displays the progress bar in a warning/paused state (typically yellow).
     */
    case Warning = 4;
}
