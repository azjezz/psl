<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function back(int $columns = 1): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer((string) $columns, ControlSequenceIntroducerKind::CursorBack);
}
