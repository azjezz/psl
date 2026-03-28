<?php

declare(strict_types=1);

namespace Psl\Ansi\Style;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 *
 * @api
 */
function double_underline(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('21', ControlSequenceIntroducerKind::SelectGraphicRendition);
}
