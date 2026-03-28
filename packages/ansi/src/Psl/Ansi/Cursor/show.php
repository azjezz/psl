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
function show(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('?25', ControlSequenceIntroducerKind::SetMode);
}
