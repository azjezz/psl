<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * Disable the kitty keyboard protocol by popping the most recent entry
 * from the keyboard mode stack.
 *
 * @see https://sw.kovidgoyal.net/kitty/keyboard-protocol/
 *
 * @pure
 */
function disable_kitty_keyboard(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('<', ControlSequenceIntroducerKind::RestoreCursor);
}
