<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use function ord;
use function strlen;

/**
 * Check whether the given text contains only printable ASCII characters (0x20-0x7E).
 *
 * @internal
 */
function is_printable_ascii(string $text): bool
{
    for ($i = 0, $len = strlen($text); $i < $len; $i++) {
        $ord = ord($text[$i]);
        if ($ord < 0x20 || $ord > 0x7E) {
            return false;
        }
    }

    return true;
}
