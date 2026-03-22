<?php

declare(strict_types=1);

namespace Psl\MIME\Part;

use Psl\IO;
use Psl\MIME\Headers;
use Psl\MIME\MediaType;

/**
 * Represents a single MIME body part as defined in RFC 2045.
 *
 * A part consists of a set of headers (including Content-Type) and a body
 * stream. Implementations may apply transfer encoding to the body automatically.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045
 *
 * @see Part for a generic implementation backed by raw headers and a read handle.
 * @see Text for a text/* part with automatic transfer-encoding.
 * @see Data for a binary attachment/inline part with base64 encoding.
 *
 * @api
 */
interface PartInterface
{
    /**
     * The Content-Type media type for this part.
     *
     * If the part has no explicit Content-Type header, implementations should
     * default to "text/plain" per RFC 2045 SS5.2.
     */
    public MediaType $mediaType { get; }

    /**
     * The complete set of MIME headers for this part.
     *
     * The returned {@see Headers} instance includes all headers required for
     * serialization (Content-Type, Content-Transfer-Encoding, etc.).
     */
    public Headers $headers { get; }

    /**
     * Return a readable stream over the (possibly transfer-encoded) body content.
     *
     * The stream may apply encoding on-the-fly (e.g., base64 or quoted-printable)
     * depending on the part implementation. Callers must consume the handle fully
     * before disposing of the part.
     */
    public function body(): IO\ReadHandleInterface;
}
