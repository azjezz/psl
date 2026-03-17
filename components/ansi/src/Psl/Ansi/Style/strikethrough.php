<?php

declare(strict_types=1);

namespace Psl\Ansi\Style;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function strikethrough(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('9', ControlSequenceIntroducerKind::SelectGraphicRendition);
}
