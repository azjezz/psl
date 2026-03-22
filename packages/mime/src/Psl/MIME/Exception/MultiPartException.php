<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

/**
 * Thrown when a multipart MIME body cannot be parsed or assembled.
 *
 * This includes invalid boundary strings, structurally malformed bodies
 * (e.g., missing closing boundary), and bodies that exceed part-count limits.
 *
 * @api
 */
final class MultiPartException extends RuntimeException
{
    /**
     * Create an exception for a boundary string that violates RFC 2046 constraints
     * (e.g., contains illegal characters or exceeds the 70-character limit).
     */
    public static function forInvalidBoundary(string $boundary): self
    {
        return new self('Invalid multipart boundary: "' . $boundary . '".');
    }

    /**
     * Create an exception for a multipart body whose structure is invalid
     * (e.g., missing delimiters, premature end of input).
     */
    public static function forMalformedMultipartBody(string $reason): self
    {
        return new self('Malformed multipart body: ' . $reason . '.');
    }

    /**
     * Create an exception when the number of parts in a multipart body exceeds the configured limit.
     *
     * @param int $limit The maximum number of parts that was exceeded.
     */
    public static function forTooManyParts(int $limit): self
    {
        return new self('Multipart body exceeds the maximum number of parts (' . $limit . ').');
    }
}
