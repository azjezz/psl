<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function scroll_up(int $lines = 1): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer((string) $lines, ControlSequenceIntroducerKind::ScrollUp);
}
