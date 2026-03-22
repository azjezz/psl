<?php

declare(strict_types=1);

namespace Psl\MIME\MultiPart;

use Psl\IO;
use Psl\MIME\Exception\EntropyException;
use Psl\MIME\Headers;
use Psl\MIME\Internal;
use Psl\MIME\MediaType;
use Psl\MIME\Parameters;
use Psl\MIME\Part\PartInterface;

/**
 * Multipart/mixed body per RFC 2046 §5.1.3.
 *
 * The main part is the primary content; additional parts are attachments.
 *
 * Named "Composite" because "Mixed" is a reserved word in PHP. This class
 * produces Content-Type: multipart/mixed bodies.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2046#section-5.1.3
 *
 * @api
 */
final class Composite implements MultiPartInterface
{
    /**
     * The boundary string used to delimit parts in the serialized body.
     *
     * @var non-empty-string
     */
    public readonly string $boundary;

    /**
     * The primary content part of this multipart/mixed body.
     */
    private PartInterface $mainPart;

    /**
     * The Content-Type media type for this multipart/mixed body, including the boundary parameter.
     */
    public readonly MediaType $mediaType;

    /**
     * The MIME headers for this multipart body, consisting of the Content-Type header.
     */
    public readonly Headers $headers;

    /**
     * The attachment parts appended after the main part.
     *
     * @var list<PartInterface>
     */
    private array $attachments = [];

    /**
     * @param null|non-empty-string $boundary
     *
     * @throws EntropyException If the system cannot generate a secure random boundary.
     */
    public function __construct(PartInterface $mainPart, null|string $boundary = null)
    {
        $this->mainPart = $mainPart;
        $this->boundary = $boundary ?? Internal\generate_boundary();
        $this->mediaType = new MediaType('multipart', 'mixed', Parameters::fromPairs([['boundary', $this->boundary]]));
        $this->headers = Headers::fromPairs([['Content-Type', $this->mediaType->toString()]]);
    }

    /**
     * Append an attachment part to this multipart/mixed body.
     *
     * Attachments are serialized after the main part in the order they are added.
     */
    public function addPart(PartInterface $part): void
    {
        $this->attachments[] = $part;
    }

    /**
     * Return a readable stream that serializes the main part followed by all attachments,
     * separated by the MIME boundary delimiter.
     */
    public function body(): IO\ReadHandleInterface
    {
        return new Internal\MultiPartReadHandle($this->boundary, [$this->mainPart, ...$this->attachments]);
    }
}
