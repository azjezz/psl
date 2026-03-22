<?php

declare(strict_types=1);

namespace Psl\MIME\Sniff\Internal;

use function strlen;
use function substr;

/**
 * Detect the specific format within a RIFF container by reading the FourCC code at bytes 8-11.
 *
 * Requires at least 12 bytes of content. Looks up the 4-byte sub-type identifier
 * in {@see RIFF_SUBTYPES} to distinguish WebP (image/webp), WAV (audio/wav),
 * and AVI (video/x-msvideo) files.
 *
 * Returns the MIME type string, or null if the content is too short or the FourCC is unrecognized.
 *
 * @internal
 */
function sniff_riff(string $content): null|string
{
    if (strlen($content) < 12) {
        return null;
    }

    $subtype = substr($content, 8, 4);

    return RIFF_SUBTYPES[$subtype] ?? null;
}
