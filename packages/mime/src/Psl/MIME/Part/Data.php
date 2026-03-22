<?php

declare(strict_types=1);

namespace Psl\MIME\Part;

use Psl\Encoding;
use Psl\IO;
use Psl\MIME\ContentDisposition;
use Psl\MIME\ContentId;
use Psl\MIME\Exception\InvalidMediaTypeComponentException;
use Psl\MIME\Headers;
use Psl\MIME\MediaType;

/**
 * Binary attachment or inline body part with base64 transfer encoding.
 *
 * Wraps a readable stream as a non-text MIME part (default: application/octet-stream),
 * automatically applying base64 encoding to the body. Supports both attachment and
 * inline dispositions via {@see ContentDisposition}, and optional Content-ID for
 * referencing from within multipart/related bodies.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045
 * @link https://datatracker.ietf.org/doc/html/rfc2183
 *
 * @see Related for embedding inline parts referenced by Content-ID.
 *
 * @api
 */
final readonly class Data implements PartInterface
{
    /**
     * The raw (unencoded) binary content handle.
     */
    private IO\ReadHandleInterface $handle;

    /**
     * The Content-Type media type for this data part (e.g. "application/octet-stream").
     */
    public MediaType $mediaType;

    /**
     * The Content-Disposition value for this part (attachment or inline).
     */
    public ContentDisposition $disposition;

    /**
     * The complete set of MIME headers for this part, including Content-Type, Content-Disposition,
     * Content-Transfer-Encoding, and optionally Content-ID.
     */
    public Headers $headers;

    /**
     * The base64-encoded body stream wrapping the raw content handle.
     */
    private IO\ReadHandleInterface $body;

    /**
     * The original filename associated with this part, if any.
     */
    public null|string $filename;

    /**
     * The Content-ID for this part, used to reference it from other parts (e.g., in HTML via "cid:" URIs).
     *
     * @see ContentId
     */
    public null|ContentId $contentId;

    /**
     * @param IO\ReadHandleInterface $handle The raw (unencoded) binary content to wrap.
     * @param null|string $filename The filename for the Content-Disposition header; null omits it.
     * @param null|MediaType $mediaType The media type for this part; defaults to "application/octet-stream".
     * @param null|ContentDisposition $disposition The disposition type; defaults to attachment with the given filename.
     * @param null|ContentId $contentId Optional Content-ID header value for inline referencing.
     *
     * @throws InvalidMediaTypeComponentException If the media type components are invalid.
     */
    public function __construct(
        IO\ReadHandleInterface $handle,
        null|string $filename = null,
        null|MediaType $mediaType = null,
        null|ContentDisposition $disposition = null,
        null|ContentId $contentId = null,
    ) {
        $this->handle = $handle;
        $this->filename = $filename;
        $this->mediaType = $mediaType ?? new MediaType('application', 'octet-stream');
        $this->disposition = $disposition ?? ContentDisposition::attachment($filename);
        $this->contentId = $contentId;

        $headers = [
            ['Content-Type', $this->mediaType->toString()],
            ['Content-Disposition', $this->disposition->toString()],
            ['Content-Transfer-Encoding', 'base64'],
        ];

        if ($this->contentId !== null) {
            $headers[] = ['Content-ID', $this->contentId->toString()];
        }

        $this->headers = Headers::fromPairs($headers);
        $this->body = new Encoding\Base64\EncodingReadHandle($this->handle, Encoding\Base64\Variant::Mime);
    }

    /**
     * Return a new {@see Data} instance with inline disposition and the given Content-ID.
     *
     * The returned part shares the same underlying read handle and media type,
     * but uses {@see ContentDisposition::inline()} and attaches the provided
     * {@see ContentId} for referencing from multipart/related root parts.
     *
     * @throws InvalidMediaTypeComponentException If the media type components are invalid.
     */
    public function asInline(ContentId $id): self
    {
        return new self(
            $this->handle,
            $this->filename,
            $this->mediaType,
            ContentDisposition::inline($this->filename),
            $id,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function body(): IO\ReadHandleInterface
    {
        return $this->body;
    }
}
