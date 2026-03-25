<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

/**
 * Thrown when the number of HTTP redirects (3xx responses) exceeds the
 * configured maximum for a single request.
 *
 * The redirect limit is set via {@see \Psl\HTTP\Client\ClientConfiguration}
 * and prevents infinite redirect loops. When this exception is thrown, the
 * client has already followed the maximum number of redirects and received
 * yet another redirect response.
 *
 * @see RuntimeException Parent exception for all HTTP client errors.
 */
final class TooManyRedirectsException extends RuntimeException
{
    /**
     * Create an exception indicating the redirect limit has been exceeded.
     *
     * @param int $count The number of redirects that were followed.
     *
     * @return self
     */
    public static function create(int $count): self
    {
        return new self('Too many redirects (' . $count . ').');
    }
}
