<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\MIME\ContentDisposition;

/**
 * Thrown when a Content-Disposition header value cannot be parsed.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2183
 *
 * @see ContentDisposition::parse()
 *
 * @api
 */
final class ContentDispositionParsingException extends ParsingException
{
    /**
     * Create an exception for an invalid Content-Disposition value.
     */
    public static function forInvalidContentDisposition(string $input): self
    {
        return new self('Invalid Content-Disposition: "' . $input . '".');
    }
}
