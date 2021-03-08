<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_detect_encoding;

/**
 * Detect the encoding of the giving string.
 *
 * @param list<string> $encoding_list
 *
 * @return null|string The string encoding or null if unable to detect encoding.
 *
 * @pure
 */
function detect_encoding(string $string, ?array $encoding_list = null): ?string
{
    if (null !== $encoding_list) {
        foreach ($encoding_list as $encoding) {
            Psl\invariant(Internal\is_encoding_valid($encoding), 'Invalid $encoding_list.');
        }
    }

    return mb_detect_encoding($string, $encoding_list, true) ?: null;
}
