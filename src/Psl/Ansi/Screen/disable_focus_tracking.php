<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function disable_focus_tracking(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('?1004', ControlSequenceIntroducerKind::ResetMode);
}
