<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function enable_mouse_tracking(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('?1000;?1006', ControlSequenceIntroducerKind::SetMode);
}
