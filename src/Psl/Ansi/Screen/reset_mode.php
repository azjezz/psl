<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function reset_mode(ScreenMode $mode): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer($mode->value, ControlSequenceIntroducerKind::ResetMode);
}
