<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * Returns the BEL (bell/alert) control character.
 *
 * @pure
 */
function bell(): ControlCharacter
{
    return new ControlCharacter("\x07");
}
