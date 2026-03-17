<?php

declare(strict_types=1);

namespace Psl\URL\Exception;

use Throwable;

/**
 * Exception thrown when a value does not conform to URL constraints.
 *
 * A valid URL requires a scheme, an authority (host), and a path that is
 * either empty or absolute (starts with '/').
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986#section-3 RFC 3986 Section 3 - Syntax Components
 */
final class InvalidURLException extends InvalidArgumentException
{
    private function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }

    /**
     * Create an exception for a URI missing its scheme component.
     */
    public static function forMissingScheme(): self
    {
        return new self('URL must have a scheme.');
    }

    /**
     * Create an exception for a URI missing its authority component.
     */
    public static function forMissingAuthority(): self
    {
        return new self('URL must have an authority (host).');
    }

    /**
     * Create an exception for a URI with a rootless (non-absolute) path.
     */
    public static function forRootlessPath(): self
    {
        return new self('URL path must be empty or absolute (starting with /).');
    }

    /**
     * Create an exception wrapping an underlying URI or IRI parsing failure.
     */
    public static function forInvalidURI(string $detail, Throwable $previous): self
    {
        return new self('Invalid URL: ' . $detail, $previous);
    }
}
