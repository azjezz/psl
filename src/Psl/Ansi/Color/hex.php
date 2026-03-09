<?php

declare(strict_types=1);

namespace Psl\Ansi\Color;

use Psl\Ansi\Exception;
use Psl\Regex;
use Psl\Str;

/**
 * @throws Exception\InvalidArgumentException If $hex is not a valid hex color string.
 *
 * @pure
 */
function hex(string $hex): Color
{
    $hex = Str\strip_prefix($hex, '#');

    if (Str\length($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (!Regex\matches($hex, '/^[0-9a-fA-F]{6}$/')) {
        throw new Exception\InvalidArgumentException('Expected a valid hex color string, got "' . $hex . '".');
    }

    $red = (int) hexdec(Str\slice($hex, 0, 2));
    $green = (int) hexdec(Str\slice($hex, 2, 2));
    $blue = (int) hexdec(Str\slice($hex, 4, 2));

    return rgb($red, $green, $blue);
}
