<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function down(int $lines = 1): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer((string) $lines, ControlSequenceIntroducerKind::CursorDown);
}
