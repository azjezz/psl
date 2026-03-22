<?php

declare(strict_types=1);

namespace Psl\MIME\MultiPart;

use Psl\IO;
use Psl\MIME\ContentDisposition;
use Psl\MIME\Exception\EntropyException;
use Psl\MIME\Headers;
use Psl\MIME\Internal;
use Psl\MIME\MediaType;
use Psl\MIME\Parameters;
use Psl\MIME\Part\Part;
use Psl\MIME\Part\PartInterface;

use function mb_strtolower;

/**
 * Builder for multipart/form-data bodies per RFC 7578.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7578
 *
 * @api
 */
final class Form implements MultiPartInterface
{
    /**
     * The boundary string used to delimit parts in the serialized body.
     *
     * @var non-empty-string
     */
    public readonly string $boundary;

    /**
     * The Content-Type media type for this multipart/form-data body, including the boundary parameter.
     */
    public readonly MediaType $mediaType;

    /**
     * The MIME headers for this multipart body, consisting of the Content-Type header.
     */
    public readonly Headers $headers;

    /**
     * The form field and file upload parts.
     *
     * @var list<PartInterface>
     */
    private array $parts = [];

    /**
     * @param null|non-empty-string $boundary
     *
     * @throws EntropyException If the system cannot generate a secure random boundary.
     */
    public function __construct(null|string $boundary = null)
    {
        $this->boundary = $boundary ?? Internal\generate_boundary();
        $this->mediaType = new MediaType('multipart', 'form-data', Parameters::fromPairs([[
            'boundary',
            $this->boundary,
        ]]));
        $this->headers = Headers::fromPairs([['Content-Type', $this->mediaType->toString()]]);
    }

    /**
     * Add a simple text field to the form.
     *
     * Creates a part with a Content-Disposition of "form-data" and the given
     * field name. The value is stored in memory as a {@see IO\MemoryHandle}.
     *
     * @param string $name The form field name, used in the Content-Disposition "name" parameter.
     * @param string $value The field value.
     */
    public function addField(string $name, string $value): void
    {
        $disposition = new ContentDisposition('form-data', Parameters::fromPairs([['name', $name]]));

        $this->parts[] = new Part(Headers::fromPairs([[
            'Content-Disposition',
            $disposition->toString(),
        ]]), new IO\MemoryHandle($value));
    }

    /**
     * Add a MIME part as a named form field.
     *
     * The part's existing headers are preserved (except Content-Disposition, which
     * is replaced with a "form-data" disposition carrying the given field name).
     * This is typically used for file uploads where the part carries Content-Type
     * and Content-Transfer-Encoding headers.
     *
     * @param string $name The form field name, used in the Content-Disposition "name" parameter.
     * @param PartInterface $part The part to include; its Content-Disposition header is overwritten.
     */
    public function addPart(string $name, PartInterface $part): void
    {
        $disposition = new ContentDisposition('form-data', Parameters::fromPairs([['name', $name]]));

        $headers = [['Content-Disposition', $disposition->toString()]];
        foreach ($part->headers->pairs() as [$headerName, $headerValue]) {
            if (mb_strtolower($headerName) === 'content-disposition') {
                continue;
            }

            $headers[] = [$headerName, $headerValue];
        }

        $this->parts[] = new Part(Headers::fromPairs($headers), $part->body());
    }

    /**
     * Return a readable stream that serializes all form fields and file parts,
     * separated by the MIME boundary delimiter.
     */
    public function body(): IO\ReadHandleInterface
    {
        return new Internal\MultiPartReadHandle($this->boundary, $this->parts);
    }
}
