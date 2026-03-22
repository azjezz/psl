<?php

declare(strict_types=1);

namespace Psl\MIME\MultiPart;

use Psl\IO;
use Psl\MIME\ContentId;
use Psl\MIME\Exception\EntropyException;
use Psl\MIME\Headers;
use Psl\MIME\Internal;
use Psl\MIME\MediaType;
use Psl\MIME\Parameters;
use Psl\MIME\Part\Part;
use Psl\MIME\Part\PartInterface;

/**
 * Builder for multipart/related bodies per RFC 2387.
 *
 * The root part is the primary content; inline parts are referenced
 * by Content-ID from within the root part (e.g., images in HTML email).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2387
 *
 * @api
 */
final class Related implements MultiPartInterface
{
    /**
     * The boundary string used to delimit parts in the serialized body.
     *
     * @var non-empty-string
     */
    public readonly string $boundary;

    /**
     * The root (primary) content part of this multipart/related body.
     */
    private PartInterface $rootPart;

    /**
     * The Content-Type media type for this multipart/related body, including the type and boundary parameters.
     */
    public readonly MediaType $mediaType;

    /**
     * The MIME headers for this multipart body, consisting of the Content-Type header.
     */
    public readonly Headers $headers;

    /**
     * The inline parts referenced by Content-ID from within the root part.
     *
     * @var list<PartInterface>
     */
    private array $parts = [];

    /**
     * @param null|non-empty-string $boundary
     *
     * @throws EntropyException If the system cannot generate a secure random boundary.
     */
    public function __construct(PartInterface $rootPart, null|string $boundary = null)
    {
        $this->rootPart = $rootPart;
        $this->boundary = $boundary ?? Internal\generate_boundary();
        $this->mediaType = new MediaType('multipart', 'related', Parameters::fromPairs([
            ['type', $this->rootPart->mediaType->essence()],
            ['boundary', $this->boundary],
        ]));
        $this->headers = Headers::fromPairs([['Content-Type', $this->mediaType->toString()]]);
    }

    /**
     * Add an inline part.
     *
     * The part should already have a Content-ID header set.
     */
    public function addPart(PartInterface $part): void
    {
        $this->parts[] = $part;
    }

    /**
     * Add an inline part referenced by Content-ID.
     *
     * The Content-ID header is added automatically.
     */
    public function addInlinePart(ContentId $id, PartInterface $part): void
    {
        $pairs = $part->headers->pairs();
        $pairs[] = ['Content-ID', $id->toString()];

        $this->parts[] = new Part(Headers::fromPairs($pairs), $part->body());
    }

    /**
     * Return a readable stream that serializes the root part followed by all
     * inline parts, separated by the MIME boundary delimiter.
     *
     * The root part appears first per RFC 2387 SS3.2, and inline parts follow
     * in the order they were added.
     */
    public function body(): IO\ReadHandleInterface
    {
        return new Internal\MultiPartReadHandle($this->boundary, [$this->rootPart, ...$this->parts]);
    }
}
