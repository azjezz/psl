<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function enable_alternate_screen(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('?1049', ControlSequenceIntroducerKind::SetMode);
}
