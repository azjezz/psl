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
 * Builder for multipart/alternative bodies per RFC 2046 §5.1.4.
 *
 * Parts are ordered from least to most preferred representation.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2046#section-5.1.4
 *
 * @api
 */
final class Alternative implements MultiPartInterface
{
    /**
     * The boundary string used to delimit parts in the serialized body.
     *
     * @var non-empty-string
     */
    public readonly string $boundary;

    /**
     * The Content-Type media type for this multipart/alternative body, including the boundary parameter.
     */
    public readonly MediaType $mediaType;

    /**
     * The MIME headers for this multipart body, consisting of the Content-Type header.
     */
    public readonly Headers $headers;

    /**
     * The alternative representation parts, ordered from least to most preferred.
     *
     * @var list<PartInterface>
     */
    private array $parts = [];

    /**
     * @param non-empty-string|null $boundary
     *
     * @throws EntropyException If the system cannot generate a secure random boundary.
     */
    public function __construct(null|string $boundary = null)
    {
        $this->boundary = $boundary ?? Internal\generate_boundary();
        $this->mediaType = new MediaType('multipart', 'alternative', Parameters::fromPairs([[
            'boundary',
            $this->boundary,
        ]]));
        $this->headers = Headers::fromPairs([['Content-Type', $this->mediaType->toString()]]);
    }

    /**
     * Add an alternative representation.
     *
     * Parts should be added from least to most preferred per RFC 2046 §5.1.4.
     */
    public function addPart(PartInterface $part): void
    {
        $this->parts[] = $part;
    }

    /**
     * Return a readable stream that serializes all alternative representations,
     * separated by the MIME boundary delimiter.
     *
     * Per RFC 2046 SS5.1.4, the last part is the most preferred representation.
     */
    public function body(): IO\ReadHandleInterface
    {
        return new Internal\MultiPartReadHandle($this->boundary, $this->parts);
    }
}
