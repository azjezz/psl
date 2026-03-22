<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\MIME\Parameters;

/**
 * Thrown when a MIME parameter cannot be parsed.
 *
 * Covers failures in parsing parameter name-value pairs, RFC 2231 encoded
 * parameters, continuation assembly, and charset conversion.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045
 * @link https://datatracker.ietf.org/doc/html/rfc2231
 *
 * @see Parameters::parse()
 *
 * @api
 */
final class ParameterParsingException extends ParsingException
{
    /**
     * Create an exception for a malformed MIME parameter.
     */
    public static function forInvalidParameter(string $input): self
    {
        return new self('Invalid parameter: "' . $input . '".');
    }
}
