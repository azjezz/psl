<?php

declare(strict_types=1);

namespace Psl\MIME\Sniff;

use Psl\MIME\Exception\InvalidMediaTypeComponentException;
use Psl\MIME\Exception\ParsingException;
use Psl\MIME\MediaType;

/**
 * Detect the MIME type from a byte string by inspecting magic bytes and content heuristics.
 *
 * The detection pipeline runs in order:
 * 1. {@see Internal\match_signatures()} - matches known magic byte signatures (images, archives, executables, etc.)
 * 2. {@see Internal\sniff_ftyp()} - detects ISO Base Media File Format containers (MP4, MOV, M4A)
 * 3. {@see Internal\sniff_text()} - identifies text-based formats (HTML, XML, JSON, SVG, shebang scripts)
 *
 * Returns `application/octet-stream` for empty or unrecognized binary content
 * per RFC 2046 (arbitrary binary data fallback).
 *
 * @throws ParsingException
 * @throws InvalidMediaTypeComponentException
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2046#section-4.5.1
 *
 * @api
 */
function from_string(string $content): MediaType
{
    if ($content === '') {
        return new MediaType('application', 'octet-stream');
    }

    $result = Internal\match_signatures($content);
    if ($result !== null) {
        return MediaType::parse($result);
    }

    $result = Internal\sniff_ftyp($content);
    if ($result !== null) {
        return MediaType::parse($result);
    }

    $result = Internal\sniff_text($content);
    if ($result !== null) {
        return MediaType::parse($result);
    }

    return new MediaType('application', 'octet-stream');
}
