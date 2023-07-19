<?php

declare(strict_types=1);

namespace Psl\Str;

use function array_map;
use function mb_detect_encoding;

/**
 * Detect the encoding of the giving string.
 *
 * @param list<Encoding> $encoding_list
 *
 * @return null|Encoding The string encoding or null if unable to detect encoding.
 *
 * @pure
 */
function detect_encoding(string $string, ?array $encoding_list = null): ?Encoding
{
    if (null !== $encoding_list) {
        $encoding_list = array_map(
            static fn(Encoding $encoding): string => $encoding->value,
            $encoding_list
        );
    }

    $encoding = mb_detect_encoding($string, $encoding_list, true) ?: null;

    return null === $encoding ? $encoding : Encoding::from($encoding);
}
