<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function disable_mouse_tracking(bool $motion = false): ControlSequenceIntroducer
{
    $mode = $motion ? '?1006;1003' : '?1006;1000';

    return new ControlSequenceIntroducer($mode, ControlSequenceIntroducerKind::ResetMode);
}
