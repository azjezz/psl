<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function up(int $lines = 1): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer((string) $lines, ControlSequenceIntroducerKind::CursorUp);
}
