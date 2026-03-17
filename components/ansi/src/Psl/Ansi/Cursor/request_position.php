<?php

declare(strict_types=1);

namespace Psl\Ansi\Cursor;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;

/**
 * @pure
 */
function request_position(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('6', ControlSequenceIntroducerKind::DeviceStatusReport);
}
