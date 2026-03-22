<?php

declare(strict_types=1);

namespace Psl\MIME\Part;

use Psl\IO;
use Psl\MIME\Headers;
use Psl\MIME\MediaType;

/**
 * Generic MIME body part backed by raw headers and a read handle.
 *
 * This is the simplest {@see PartInterface} implementation: it stores headers
 * and a body stream verbatim without applying any transfer encoding. It is
 * typically produced by {@see \Psl\MIME\MultiPart\Parser} when parsing
 * multipart bodies, or used internally to wrap parts with modified headers.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045
 *
 * @api
 */
final readonly class Part implements PartInterface
{
    /**
     * The Content-Type media type for this data part.
     */
    public MediaType $mediaType;

    /**
     * The complete set of MIME headers for this part.
     */
    public Headers $headers;

    /**
     * @param Headers $headers The MIME headers for this part.
     * @param IO\ReadHandleInterface $body The readable stream over the part body content.
     */
    public function __construct(
        Headers $headers,
        public IO\ReadHandleInterface $body,
    ) {
        $this->headers = $headers;

        $value = $this->headers->get('content-type');
        if ($value !== null) {
            $this->mediaType = MediaType::parse($value);
        } else {
            $this->mediaType = new MediaType('text', 'plain');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function body(): IO\ReadHandleInterface
    {
        return $this->body;
    }
}
