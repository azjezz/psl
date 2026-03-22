<?php

declare(strict_types=1);

namespace Psl\MIME\Part;

use Psl\Encoding;
use Psl\IO;
use Psl\MIME\Exception\InvalidMediaTypeComponentException;
use Psl\MIME\Headers;
use Psl\MIME\MediaType;
use Psl\MIME\Parameters;
use Psl\MIME\TransferEncoding;

/**
 * Text body part with automatic transfer encoding.
 *
 * Wraps a readable stream as a text/* MIME part, applying the specified
 * {@see TransferEncoding} on-the-fly when the body is read. Defaults to
 * quoted-printable encoding, which is optimal for human-readable text with
 * occasional non-ASCII characters.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045
 * @link https://datatracker.ietf.org/doc/html/rfc2046#section-4.1
 *
 * @api
 */
final readonly class Text implements PartInterface
{
    /**
     * The transfer encoding applied to this text part's body.
     */
    public TransferEncoding $encoding;

    /**
     * The parsed Content-Type media type for this text part (e.g. "text/plain; charset=utf-8").
     */
    public MediaType $mediaType;

    /**
     * The complete set of MIME headers for this part, including Content-Type and Content-Transfer-Encoding.
     */
    public Headers $headers;

    /**
     * The transfer-encoded body stream.
     *
     * For base64 and quoted-printable encodings, this wraps the original handle
     * in the corresponding encoding read handle. For 7bit, 8bit, and binary
     * encodings, this is the original handle passed through unchanged.
     */
    private IO\ReadHandleInterface $body;

    /**
     * @param IO\ReadHandleInterface $handle The raw (unencoded) text content to wrap.
     * @param string $subtype The text subtype (e.g., "plain", "html", "csv").
     * @param string $charset The character set for the Content-Type parameter (e.g., "utf-8", "iso-8859-1").
     * @param null|TransferEncoding $encoding The transfer encoding to apply; defaults to quoted-printable.
     *
     * @throws InvalidMediaTypeComponentException If the subtype is not a valid MIME type component.
     */
    public function __construct(
        IO\ReadHandleInterface $handle,
        string $subtype = 'plain',
        string $charset = 'utf-8',
        null|TransferEncoding $encoding = null,
    ) {
        $this->encoding = $encoding ?? TransferEncoding::QuotedPrintable;
        $this->mediaType = new MediaType('text', $subtype, Parameters::fromPairs([['charset', $charset]]));
        $this->headers = Headers::fromPairs([
            ['Content-Type', $this->mediaType->toString()],
            ['Content-Transfer-Encoding', $this->encoding->value],
        ]);
        $this->body = match ($this->encoding) {
            TransferEncoding::Base64 => new Encoding\Base64\EncodingReadHandle($handle, Encoding\Base64\Variant::Mime),
            TransferEncoding::QuotedPrintable => new Encoding\QuotedPrintable\EncodingReadHandle($handle),
            TransferEncoding::SevenBit, TransferEncoding::EightBit, TransferEncoding::Binary => $handle,
        };
    }

    /**
     * {@inheritDoc}
     */
    public function body(): IO\ReadHandleInterface
    {
        return $this->body;
    }
}
