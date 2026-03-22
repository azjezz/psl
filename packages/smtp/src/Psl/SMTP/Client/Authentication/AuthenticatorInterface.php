<?php

declare(strict_types=1);

namespace Psl\SMTP\Client\Authentication;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\SMTP\Client\ConnectionInterface;
use Psl\SMTP\Exception\AuthenticationException;

/**
 * Contract for SMTP authentication mechanisms per RFC 4954.
 *
 * Implementations handle a specific SASL mechanism (PLAIN, LOGIN, XOAUTH2, etc.)
 * by exchanging the required commands on a live connection.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4954
 *
 * @api
 */
interface AuthenticatorInterface
{
    /**
     * The SMTP AUTH mechanism name (e.g., "PLAIN", "LOGIN", "XOAUTH2").
     */
    public string $mechanism { get; }

    /**
     * Perform authentication on the given connection.
     *
     * @throws AuthenticationException If authentication fails.
     */
    public function authenticate(
        ConnectionInterface $connection,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void;
}
