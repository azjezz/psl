<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function save(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('', ControlSequenceIntroducerKind::SaveCursor);
}
