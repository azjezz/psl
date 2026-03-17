<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use function chr;
use function intval;
use function strlen;
use function substr;

/**
 * Q-decode an encoded text per RFC 2047 §4.2.
 *
 * @internal
 */
function q_decode(string $text): string
{
    $result = '';
    $len = strlen($text);
    $i = 0;

    while ($i < $len) {
        $char = $text[$i];

        if ($char === '_') {
            $result .= ' ';
            $i++;

            continue;
        }

        if ($char === '=' && ($i + 2) < $len) {
            $hex = substr($text, $i + 1, 2);
            if ($hex !== '') {
                $result .= chr(intval($hex, 16));
            }

            $i += 3;

            continue;
        }

        $result .= $char;
        $i++;
    }

    return $result;
}
