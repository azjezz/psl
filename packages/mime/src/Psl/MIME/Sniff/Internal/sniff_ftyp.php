<?php

declare(strict_types=1);

namespace Psl\MIME\Sniff\Internal;

use function strlen;
use function substr;

/**
 * Detect ISO Base Media File Format containers (MP4, QuickTime, M4A) by looking for an ftyp box.
 *
 * Requires at least 12 bytes of content. Checks for the `ftyp` marker at bytes 4-7,
 * then reads the 4-byte major brand at bytes 8-11 and looks it up in {@see FTYP_BRANDS}.
 * Falls back to `video/mp4` for unrecognized brands that still have a valid ftyp box.
 *
 * Returns the MIME type string, or null if no ftyp box is present or the content is too short.
 *
 * @internal
 */
function sniff_ftyp(string $content): null|string
{
    if (strlen($content) < 12) {
        return null;
    }

    if (substr($content, 4, 4) !== 'ftyp') {
        return null;
    }

    $brand = substr($content, 8, 4);

    return namespace\FTYP_BRANDS[$brand] ?? 'video/mp4';
}
