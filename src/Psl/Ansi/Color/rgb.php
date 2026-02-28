<?php

declare(strict_types=1);

namespace Psl\Ansi\Color;

use Psl\Ansi\Exception;

/**
 * @throws Exception\InvalidArgumentException If any component is not in the range 0-255.
 *
 * @pure
 */
function rgb(int $red, int $green, int $blue): Color
{
    return Color::rgb($red, $green, $blue);
}
