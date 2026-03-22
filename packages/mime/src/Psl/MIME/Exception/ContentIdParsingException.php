<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\MIME\ContentId;

/**
 * Thrown when a Content-ID header value does not conform to RFC 2392 syntax.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2392
 *
 * @see ContentId::parse()
 *
 * @api
 */
final class ContentIdParsingException extends ParsingException
{
    /**
     * Create an exception for an invalid Content-ID value.
     */
    public static function forInvalidContentId(string $input): self
    {
        return new self('Invalid Content-ID: "' . $input . '".');
    }
}
