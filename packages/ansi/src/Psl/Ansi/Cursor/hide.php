<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 *
 * @api
 */
function hide(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('?25', ControlSequenceIntroducerKind::ResetMode);
}
