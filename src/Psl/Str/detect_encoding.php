<?php

declare(strict_types=1);

namespace Psl\Str;

use function array_map;
use function mb_detect_encoding;

/**
 * Detect the encoding of the giving string.
 *
 * @param null|list<Encoding> $encodingList
 *
 * @return null|Encoding The string encoding or null if unable to detect encoding.
 *
 * @pure
 */
function detect_encoding(string $string, null|array $encodingList = null): null|Encoding
{
    if (null !== $encodingList) {
        $encodingList = array_map(static fn(Encoding $encoding): string => $encoding->value, $encodingList);
    }

    $encoding = mb_detect_encoding($string, $encodingList, true);
    if (false === $encoding) {
        return null;
    }

    return Encoding::from($encoding);
}
