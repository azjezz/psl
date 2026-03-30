<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Default\DefaultInterface;
use Psl\DNS\Record\RecordType;
use Psl\DNS\System\HostsFile;
use Psl\TCP;

use function count;

/**
 * Resolver that mirrors the operating system's DNS behavior.
 *
 * Loads the OS-specific DNS config and builds a resolver chain:
 * - Hosts file lookup (no network)
 * - Search domain expansion for short names
 * - Split-horizon routing for domain-scoped nameservers (macOS)
 * - Multiple global nameservers raced for fastest response
 * - UDP with automatic TCP fallback on truncation
 *
 * Can be used as a default parameter value:
 *
 *     function fetch(string $url, ResolverInterface $resolver = new SystemResolver()): Response
 *
 * @api
 *
 * @codeCoverageIgnore
 */
final class SystemResolver implements ResolverInterface, DefaultInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * The composed resolver chain built from system DNS configuration.
     */
    private readonly ResolverInterface $inner;

    /**
     * @param bool $dnssec Whether to set the DNSSEC OK (DO) flag in queries.
     * @param bool $udp Whether to try UDP before TCP. When false, only TCP is used, combine with a TLS connector for full DNS-over-TLS (DoT).
     * @param int $udpPayloadSize Maximum UDP payload size for EDNS0 (ignored when $udp is false).
     * @param TCP\ConnectorInterface $connector The connector used for TCP/DoT connections.
     *
     * @throws Exception\SystemException If the system configuration cannot be loaded.
     */
    public function __construct(
        bool $dnssec = true,
        bool $udp = true,
        int $udpPayloadSize = 1232,
        TCP\ConnectorInterface $connector = new TCP\Connector(),
    ) {
        $config = System\Settings::load();

        $globalResolvers = [];
        $routes = [];
        foreach ($config->nameservers as $entry) {
            $tcpResolver = new TCPResolver($entry->host, $entry->port, $dnssec, $connector);

            $entryResolver = $udp
                ? new FallbackResolver([
                    new UDPResolver($entry->host, $entry->port, $dnssec, $udpPayloadSize),
                    $tcpResolver,
                ])
                : $tcpResolver;

            if ($entry->forDomains === []) {
                $globalResolvers[] = $entryResolver;
            } else {
                $routes[] = new Route($entry->forDomains, $entryResolver);
            }
        }

        if ($globalResolvers === []) {
            throw Exception\SystemException::forNoNameservers();
        }

        /** @var non-empty-list<ResolverInterface> $globalResolvers */
        $default = count($globalResolvers) === 1 ? $globalResolvers[0] : new RacingResolver($globalResolvers);

        $resolver = $routes === [] ? $default : new SplitHorizonResolver($routes, $default);

        if ($config->searchDomains !== []) {
            $resolver = new SearchDomainResolver($resolver, $config->searchDomains);
        }

        try {
            $hostsFile = HostsFile\HostsFile::load();
            if ($hostsFile->entries !== []) {
                $resolver = new HostsFileResolver($resolver, $hostsFile);
            }
        } catch (Exception\SystemException) {
            // @mago-expect lint:no-empty-catch-clause - fine.
        }

        $this->inner = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public static function default(): static
    {
        return new static();
    }

    /**
     * {@inheritDoc}
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        return $this->inner->query($name, $type, $cancellation, $ednsOptions);
    }
}
