<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function erase_line(LineEraseMode $mode = LineEraseMode::Right): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer((string) $mode->value, ControlSequenceIntroducerKind::EraseInLine);
}
