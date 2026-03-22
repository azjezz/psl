<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Throwable;

/**
 * Thrown when a Content-Transfer-Encoding operation (base64, quoted-printable, etc.) fails.
 *
 * This covers both decoding of incoming data and streaming encode/decode pipelines.
 *
 * @api
 */
final class EncodingException extends RuntimeException
{
    /**
     * Create an exception when the input data is structurally invalid for the declared encoding
     * (e.g., illegal characters in a base64 stream).
     */
    public static function forInvalidDecodingInput(string $message): self
    {
        return new self('Failed to decode content-transfer-encoding: ' . $message);
    }

    /**
     * Create an exception wrapping a lower-level failure that occurred during decoding.
     */
    public static function forDecodingFailure(Throwable $previous): self
    {
        return new self('Failed to decode content-transfer-encoding: ' . $previous->getMessage(), $previous);
    }

    /**
     * Create an exception wrapping a lower-level I/O failure during streamed encoding or decoding.
     */
    public static function forStreamFailure(Throwable $previous): self
    {
        return new self('Stream encoding/decoding failure: ' . $previous->getMessage(), $previous);
    }
}
