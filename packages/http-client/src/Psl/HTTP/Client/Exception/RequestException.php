<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

use Throwable;

/**
 * Thrown when the request itself is invalid or cannot be dispatched.
 *
 * This exception indicates a problem with the request before it reaches the wire,
 * such as a missing URL or a structurally invalid message.
 *
 * @see RuntimeException Parent exception for all HTTP client errors.
 */
final class RequestException extends RuntimeException
{
    /**
     * Create an exception for a request that has no URL and no base URL is configured.
     *
     * @param null|Throwable $previous Optional underlying cause.
     *
     * @return self
     */
    public static function forMissingUrl(null|Throwable $previous = null): self
    {
        return new self(
            'Request URL could not be resolved: no URL on the request and no base URL in client configuration.',
            previous: $previous,
        );
    }

    /**
     * Create an exception for a structurally invalid request.
     *
     * @param string $detail Human-readable description of why the request is invalid.
     *
     * @return self
     */
    public static function forInvalidRequest(string $detail): self
    {
        return new self('Invalid request: ' . $detail);
    }
}
