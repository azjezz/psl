<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use function ord;
use function strlen;

/**
 * Determine whether B-encoding should be used based on the proportion of non-ASCII bytes.
 *
 * @internal
 */
function should_use_b_encoding(string $text): bool
{
    $nonAscii = 0;
    $len = strlen($text);

    for ($i = 0; $i < $len; $i++) {
        $ord = ord($text[$i]);
        if ($ord < 0x20 || $ord > 0x7E) {
            $nonAscii++;
        }
    }

    return ($nonAscii / $len) > 0.3;
}
