<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function enable_mouse_tracking(bool $motion = false): ControlSequenceIntroducer
{
    $mode = $motion ? '?1003;1006' : '?1000;1006';

    return new ControlSequenceIntroducer($mode, ControlSequenceIntroducerKind::SetMode);
}
