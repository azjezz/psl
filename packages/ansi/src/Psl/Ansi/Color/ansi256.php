<?php

declare(strict_types=1);

namespace Psl\Ansi\Color;

use Psl\Ansi\Exception;

/**
 * @throws Exception\InvalidArgumentException If $code is not in the range 0-255.
 *
 * @pure
 */
function ansi256(int $code): Color
{
    return Color::ansi256($code);
}
