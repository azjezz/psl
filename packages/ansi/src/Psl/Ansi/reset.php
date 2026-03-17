<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * Returns the SGR reset sequence.
 *
 * @pure
 */
function reset(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('0', ControlSequenceIntroducerKind::SelectGraphicRendition);
}
