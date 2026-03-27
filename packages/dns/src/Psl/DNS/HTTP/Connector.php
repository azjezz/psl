<?php

declare(strict_types=1);

namespace Psl\DNS\HTTP;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResolverInterface;
use Psl\DNS\ResponseCode;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Connection\ConnectorInterface;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Message\Request;
use Psl\Network;
use Psl\URL;

use function filter_var;
use function str_contains;
use function trim;

use const FILTER_VALIDATE_IP;

/**
 * HTTP connector decorator that resolves hostnames via a DNS resolver before
 * delegating to an inner connector.
 *
 * When the origin host is a hostname (not an IP address), this connector
 * queries the DNS resolver for A (IPv4) or AAAA (IPv6) records and creates
 * a new origin with the resolved IP address. The inner connector then
 * connects to the resolved IP directly, bypassing OS-level DNS resolution.
 *
 * When the origin host is already an IP address, the connector delegates
 * to the inner connector without performing any DNS query.
 *
 * This enables:
 * - Async DNS resolution with connection pooling via {@see DNS\UDPResolver} or {@see DNS\TCPResolver}.
 * - DNS-over-TLS for encrypted name resolution.
 * - DNS-over-HTTPS via {@see DNS\HTTPSResolver} for censorship-resistant resolution.
 * - Caching with TTL-aware eviction via {@see DNS\CachedResolver}.
 * - Deterministic resolution in tests via {@see DNS\StaticResolver}.
 *
 * Pair with {@see DNS\CachedResolver} to avoid redundant lookups across
 * requests to the same host.
 *
 * @api
 */
final readonly class Connector implements ConnectorInterface
{
    public function __construct(
        private ConnectorInterface $connector,
        private ResolverInterface $resolver,
    ) {}

    /**
     * Resolve the origin hostname via DNS and delegate to the inner connector.
     *
     * If the origin host is already an IP address, no DNS query is performed.
     * Otherwise, the resolver is queried for an A record (IPv4). If no A record
     * is found, an AAAA query (IPv6) is attempted. The first resolved address
     * is used to create a new origin that the inner connector connects to.
     *
     * {@inheritDoc}
     */
    #[Override]
    public function connect(
        Origin $origin,
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): ConnectionInterface {
        $resolvedOrigin = $this->resolveOrigin($origin, $cancellation);
        $configuration = $this->resolveHttpProxyHostname($configuration, $cancellation);
        $configuration = $this->resolveSocksProxyHostname($configuration, $cancellation);

        return $this->connector->connect($resolvedOrigin, $request, $configuration, $cancellation);
    }

    /**
     * Resolve the origin hostname to an IP address if it is not already one.
     */
    private function resolveOrigin(Origin $origin, CancellationTokenInterface $cancellation): Origin
    {
        $host = trim($origin->host, '[]');
        if (false !== filter_var($host, FILTER_VALIDATE_IP)) {
            return $origin;
        }

        $resolved = $this->resolveHostname($origin->host, $cancellation);
        if (str_contains($resolved, ':')) {
            $resolved = '[' . $resolved . ']';
        }

        return new Origin($origin->scheme, $resolved, $origin->port, sniHost: $origin->host);
    }

    /**
     * Resolve the proxy hostname in the configuration if one is set.
     *
     * Resolves the proxy URL hostname to an IP address, builds a new URL
     * with the resolved IP, and sets the SNI to the original hostname for
     * TLS certificate validation. If the proxy URL already contains an IP
     * address or no proxy is configured, the configuration is returned
     * unchanged.
     */
    private function resolveHttpProxyHostname(
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation,
    ): ClientConfiguration {
        $proxyConfiguration = $configuration->proxyConfiguration;
        if ($proxyConfiguration === null) {
            return $configuration;
        }

        $proxyHost = (string) $proxyConfiguration->url->authority->host;
        $strippedHost = trim($proxyHost, '[]');
        if (false !== filter_var($strippedHost, FILTER_VALIDATE_IP)) {
            return $configuration;
        }

        $resolved = $this->resolveHostname($proxyHost, $cancellation);

        $port = $proxyConfiguration->url->authority->port;
        $scheme = $proxyConfiguration->url->scheme;
        $resolvedUrl = URL\parse($scheme . '://' . $resolved . ($port !== null ? ':' . $port : ''));

        $newProxy = new ProxyConfiguration(
            $resolvedUrl,
            $proxyConfiguration->authorization,
            $proxyConfiguration->sni ?? $proxyHost,
            $proxyConfiguration->skipProxyFor,
        );

        return $configuration->withProxyConfiguration($newProxy);
    }

    /**
     * Resolve the SOCKS proxy hostname in the configuration if one is set.
     */
    private function resolveSocksProxyHostname(
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation,
    ): ClientConfiguration {
        $socketConfiguration = $configuration->socksConfiguration;
        if ($socketConfiguration === null) {
            return $configuration;
        }

        $proxyHost = $socketConfiguration->proxyHost;
        $strippedHost = trim($proxyHost, '[]');
        if (false !== filter_var($strippedHost, FILTER_VALIDATE_IP)) {
            return $configuration;
        }

        $resolved = $this->resolveHostname($proxyHost, $cancellation);

        return $configuration->withSocksConfiguration($socketConfiguration->withProxyHost($resolved));
    }

    /**
     * Resolve a hostname to an IP address string.
     *
     * Tries A (IPv4) first, falls back to AAAA (IPv6).
     *
     * @param non-empty-string $host The hostname to resolve.
     *
     * @return non-empty-string The resolved IP address.
     *
     * @throws Network\Exception\RuntimeException If the hostname cannot be resolved.
     */
    private function resolveHostname(string $host, CancellationTokenInterface $cancellation): string
    {
        $response = $this->resolver->query($host, RecordType::A, $cancellation);
        if ($response->code === ResponseCode::NoError) {
            foreach ($response->answers as $record) {
                if ($record instanceof ARecord) {
                    return $record->address->toString();
                }
            }
        }

        $response = $this->resolver->query($host, RecordType::AAAA, $cancellation);
        if ($response->code === ResponseCode::NoError) {
            foreach ($response->answers as $record) {
                if ($record instanceof AAAARecord) {
                    return $record->address->toString();
                }
            }
        }

        throw new Network\Exception\RuntimeException(
            'DNS resolution failed for "' . $host . '": no A or AAAA records found.',
        );
    }
}
