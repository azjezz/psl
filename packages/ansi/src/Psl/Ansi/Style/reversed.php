<?php

declare(strict_types=1);

namespace Psl\Ansi\Style;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function reversed(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('7', ControlSequenceIntroducerKind::SelectGraphicRendition);
}
