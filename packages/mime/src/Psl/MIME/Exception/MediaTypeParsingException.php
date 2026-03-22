<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\MIME\MediaType;

/**
 * Thrown when a media type string does not conform to RFC 2045 / RFC 6838 syntax.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045
 * @link https://datatracker.ietf.org/doc/html/rfc6838
 *
 * @see MediaType::parse()
 *
 * @api
 */
final class MediaTypeParsingException extends ParsingException
{
    /**
     * Create an exception for a media type string that cannot be parsed.
     */
    public static function forInvalidMediaType(string $input): self
    {
        return new self('Invalid media type: "' . $input . '".');
    }
}
