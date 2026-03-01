<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * Enable in-band resize notifications (mode 2048).
 *
 * When enabled, the terminal sends resize events as escape sequences
 * instead of (or in addition to) SIGWINCH signals.
 *
 * Supported by Ghostty, Kitty, iTerm2, foot, and others.
 *
 * @pure
 */
function enable_in_band_resize(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('?2048', ControlSequenceIntroducerKind::SetMode);
}
