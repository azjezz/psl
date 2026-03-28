<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

use Psl\HTTP\Client;

/**
 * Thrown when the number of HTTP redirects (3xx responses) exceeds the
 * configured maximum for a single request.
 *
 * The redirect limit is set via the {@see Client\RedirectClient}
 * constructor parameter (default: 10) and prevents infinite redirect loops.
 * When this exception is thrown, the client has already followed the maximum
 * number of redirects and received yet another 3xx response with a Location
 * header.
 *
 * This exception is thrown exclusively by {@see Client\RedirectClient},
 * the decorator responsible for automatic redirect following. Clients that do
 * not use {@see Client\RedirectClient} will never encounter this
 * exception; they will instead receive the raw 3xx response in the transaction.
 *
 * @see RuntimeException Parent exception for all HTTP client errors.
 * @see Client\RedirectClient The decorator that enforces the redirect limit.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4 Redirection 3xx
 *
 * @api
 */
final class TooManyRedirectsException extends RuntimeException
{
    /**
     * Create an exception indicating the redirect limit has been exceeded.
     *
     * @param int $count The number of redirects that were followed before
     *                    the limit was reached.
     */
    public static function create(int $count): self
    {
        return new self('Too many redirects (' . $count . ').');
    }
}
