<?php

declare(strict_types=1);

namespace Psl\Ansi\Style;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function dim(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('2', ControlSequenceIntroducerKind::SelectGraphicRendition);
}
