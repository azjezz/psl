<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * Returns the SGR reset sequence.
 *
 * @pure
 *
 * @api
 */
function reset(): ControlSequenceIntroducer
{
    return new ControlSequenceIntroducer('0', ControlSequenceIntroducerKind::SelectGraphicRendition);
}
