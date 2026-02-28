<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function restore(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('', ControlSequenceIntroducerKind::RestoreCursor);
}
