<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Middleware;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\CIDR;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IP;

/**
 * Connection-level middleware that blocks requests to denied IP addresses and CIDR ranges.
 *
 * Provides Server-Side Request Forgery (SSRF) protection by inspecting the resolved
 * peer address of an established connection before the HTTP exchange takes place. If
 * the peer IP matches any entry in the denied list, a {@see RuntimeException} is thrown
 * and the exchange is never performed.
 *
 * ## How IP matching works
 *
 * After the connection is established (DNS resolved, TCP connected, TLS handshake
 * complete), this middleware extracts the peer IP address from
 * {@see ConnectionInterface::$metadata} via the peer address. It then checks this
 * address against the denied list, which can contain:
 *
 * - **Individual IP addresses** ({@see IP\Address}): Exact match against the peer IP.
 * - **CIDR blocks** ({@see CIDR\Block}): Range match; the peer IP is checked for
 *   containment within the CIDR range.
 *
 * Both IPv4 and IPv6 addresses are supported, including IPv4-mapped IPv6 addresses
 * (e.g., `::ffff:127.0.0.1`).
 *
 * ## Why connection-level middleware
 *
 * SSRF protection must operate on the resolved IP address, not the hostname from the
 * URL. Hostname-based checks are insufficient because:
 *
 * - DNS rebinding attacks can cause a hostname to resolve to an internal IP.
 * - The server may be behind a load balancer or CDN, making the hostname unreliable.
 * - Multiple A/AAAA records may resolve to different addresses.
 *
 * By checking the peer address after connection establishment, this middleware inspects
 * the actual IP address the connection was made to, which is immune to DNS rebinding.
 *
 * ## Preset: private network ranges
 *
 * The {@see forPrivateNetworkRanges()} factory method returns a preconfigured instance
 * that blocks all private, loopback, link-local, and unspecified addresses for both
 * IPv4 and IPv6, including their IPv4-mapped IPv6 equivalents. This is suitable for
 * any application that fetches user-supplied URLs.
 *
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Server_Side_Request_Forgery_Prevention_Cheat_Sheet.html OWASP SSRF Prevention
 *
 * @api
 */
final readonly class DeniedDestinationsMiddleware implements MiddlewareInterface
{
    /**
     * Create a new denied destinations middleware with the given deny list.
     *
     * @param list<CIDR\Block|IP\Address> $denied List of IP addresses and CIDR ranges to deny. An empty list disables the check (all destinations are allowed).
     */
    public function __construct(
        private array $denied,
    ) {}

    /**
     * Create a preset that denies all private, loopback, link-local, and unspecified IP ranges.
     *
     * This covers the most common SSRF attack vectors by blocking connections to:
     *
     * - **IPv4 loopback** (`127.0.0.0/8`): localhost and loopback addresses.
     * - **IPv4 private** (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`): RFC 1918 private networks.
     * - **IPv4 link-local** (`169.254.0.0/16`): APIPA and cloud metadata endpoints (e.g., AWS `169.254.169.254`).
     * - **IPv4 unspecified** (`0.0.0.0`): the unspecified address.
     * - **IPv6 loopback** (`::1`): the IPv6 loopback address.
     * - **IPv6 unique local** (`fc00::/7`): RFC 4193 unique local addresses.
     * - **IPv6 link-local** (`fe80::/10`): IPv6 link-local addresses.
     * - **IPv6 unspecified** (`::`): the IPv6 unspecified address.
     * - **IPv4-mapped IPv6**: The IPv6 equivalents of the above IPv4 ranges (e.g., `::ffff:127.0.0.0/104`).
     *
     * Suitable for any application that fetches user-supplied URLs and needs to prevent
     * access to internal network resources.
     */
    public static function forPrivateNetworkRanges(): self
    {
        return new self([
            // IPv4 loopback
            new CIDR\Block('127.0.0.0/8'),
            // IPv4 private
            new CIDR\Block('10.0.0.0/8'),
            new CIDR\Block('172.16.0.0/12'),
            new CIDR\Block('192.168.0.0/16'),
            // IPv4 link-local
            new CIDR\Block('169.254.0.0/16'),
            // IPv4 unspecified
            IP\Address::v4('0.0.0.0'),
            // IPv6 loopback
            IP\Address::v6('::1'),
            // IPv6 private (unique local)
            new CIDR\Block('fc00::/7'),
            // IPv6 link-local
            new CIDR\Block('fe80::/10'),
            // IPv6 unspecified
            IP\Address::v6('::'),
            // IPv4-mapped IPv6 loopback
            new CIDR\Block('::ffff:127.0.0.0/104'),
            // IPv4-mapped IPv6 private
            new CIDR\Block('::ffff:10.0.0.0/104'),
            new CIDR\Block('::ffff:172.16.0.0/108'),
            new CIDR\Block('::ffff:192.168.0.0/112'),
        ]);
    }

    /**
     * Check the connection's peer address against the deny list and delegate to the next handler.
     *
     * Extracts the peer IP address from {@see ConnectionInterface::$metadata} and checks
     * it against every entry in the deny list. The check is performed in order; the first
     * matching entry causes the middleware to throw immediately, before any HTTP exchange
     * takes place. If the deny list is empty or no entry matches, the request is delegated
     * to the next handler in the chain via {@see HandlerInterface::handle()}.
     *
     * For individual {@see IP\Address} entries, an exact equality check is performed.
     * For {@see CIDR\Block} entries, a containment check determines whether the peer
     * IP falls within the CIDR range.
     *
     * When a destination is denied, the thrown {@see RuntimeException} carries a message
     * in the format: "Connection to {ip} is denied by client configuration."
     *
     * @inheritDoc
     *
     * @throws RuntimeException If the peer IP address matches any entry in the deny list.
     */
    #[Override]
    public function process(
        ConnectionInterface $connection,
        Request $request,
        ClientConfiguration $configuration,
        HandlerInterface $handler,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction {
        $peerHost = $connection->metadata->peerAddress->host;

        if ($this->denied !== []) {
            $address = IP\Address::parse($peerHost);
            foreach ($this->denied as $denied) {
                if ($denied instanceof CIDR\Block) {
                    if ($denied->contains($address)) {
                        throw new RuntimeException(
                            'Connection to ' . $peerHost . ' is denied by client configuration.',
                        );
                    }
                } elseif ($denied->equals($address)) {
                    throw new RuntimeException('Connection to ' . $peerHost . ' is denied by client configuration.');
                }
            }
        }

        return $handler->handle($connection, $request, $configuration, $cancellation);
    }
}
