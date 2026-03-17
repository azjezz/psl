<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function erase(EraseMode $mode = EraseMode::Below): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer((string) $mode->value, ControlSequenceIntroducerKind::EraseInDisplay);
}
