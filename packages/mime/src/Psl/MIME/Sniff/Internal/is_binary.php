<?php

declare(strict_types=1);

namespace Psl\MIME\Sniff\Internal;

use function min;
use function ord;
use function str_contains;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Heuristically determine whether content is binary by sampling the first 512 bytes.
 *
 * The check uses two criteria:
 * 1. Presence of null bytes (0x00) -- indicates binary, unless the content starts with a
 *    UTF-16 BOM (0xFF 0xFE or 0xFE 0xFF), in which case null bytes are expected.
 * 2. Proportion of non-text bytes -- if more than 1/8 of the sampled bytes fall outside
 *    the printable/whitespace range (below 0x08, or between 0x0E-0x1F excluding ESC 0x1B),
 *    the content is classified as binary.
 *
 * @internal
 */
function is_binary(string $content): bool
{
    $sampleLen = min(strlen($content), 512);
    $sample = substr($content, 0, $sampleLen);

    if (str_contains($sample, "\x00")) {
        if (str_starts_with($sample, "\xFF\xFE") || str_starts_with($sample, "\xFE\xFF")) {
            return false;
        }

        return true;
    }

    $nonText = 0;
    for ($i = 0; $i < $sampleLen; $i++) {
        $byte = ord($sample[$i]);
        if ($byte < 0x08 || $byte > 0x0d && $byte < 0x20 && $byte !== 0x1b) {
            $nonText++;
        }
    }

    return $nonText > ($sampleLen / 8);
}
