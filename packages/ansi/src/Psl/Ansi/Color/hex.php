<?php

declare(strict_types=1);

namespace Psl\Ansi\Color;

use Psl\Ansi\Exception;

use function hexdec;
use function preg_match;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * @throws Exception\InvalidArgumentException If $hex is not a valid hex color string.
 *
 * @pure
 */
function hex(string $hex): Color
{
    if (str_starts_with($hex, '#')) {
        $hex = substr($hex, 1);
    }

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        throw new Exception\InvalidArgumentException('Expected a valid hex color string, got "' . $hex . '".');
    }

    $red = (int) hexdec(substr($hex, 0, 2));
    $green = (int) hexdec(substr($hex, 2, 2));
    $blue = (int) hexdec(substr($hex, 4, 2));

    return namespace\rgb($red, $green, $blue);
}
