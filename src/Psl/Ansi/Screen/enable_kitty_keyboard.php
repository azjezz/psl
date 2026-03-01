<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * Enable the kitty keyboard protocol with the given flags.
 *
 * Pushes a new entry onto the keyboard mode stack. Use {@see disable_kitty_keyboard()}
 * to pop it and restore the previous mode.
 *
 * Common flags:
 *  - 1: Disambiguate escape codes (shift+enter, etc.)
 *  - 2: Report event types (press, repeat, release)
 *  - 4: Report alternate keys
 *  - 8: Report all keys as escape codes
 *  - 16: Report associated text
 *
 * Flags can be combined by adding them together (e.g. 3 = disambiguate + event types).
 *
 * @param positive-int $flags
 *
 * @see https://sw.kovidgoyal.net/kitty/keyboard-protocol/
 *
 * @pure
 */
function enable_kitty_keyboard(int $flags = 1): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('>' . $flags, ControlSequenceIntroducerKind::RestoreCursor);
}
