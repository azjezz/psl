<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function move_to(int $row, int $column): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer($row . ';' . $column, ControlSequenceIntroducerKind::CursorMoveTo);
}
