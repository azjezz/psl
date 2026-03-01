<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * Disable in-band resize notifications (mode 2048).
 *
 * @pure
 */
function disable_in_band_resize(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('?2048', ControlSequenceIntroducerKind::ResetMode);
}
