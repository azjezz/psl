<?php

declare(strict_types=1);

namespace Psl\DNS\System\HostsFile;

use Psl\DNS\Exception;
use Psl\IP\Address;

use function strtolower;

/**
 * Parsed hosts file entries.
 *
 * Maps lowercased hostnames to their IP addresses.
 *
 * @api
 */
final readonly class HostsFile
{
    /**
     * @param array<string, list<Address>> $entries
     *  Map of lowercased hostname to list of IP addresses.
     */
    public function __construct(
        public array $entries,
    ) {}

    /**
     * Load and parse the system hosts file.
     *
     * Reads the OS-specific hosts file via non-blocking process execution:
     * - Linux/macOS/FreeBSD: `/etc/hosts`
     * - Windows: `%SystemRoot%\system32\drivers\etc\hosts`
     *
     * @throws Exception\SystemException If the hosts file cannot be read.
     */
    public static function load(): self
    {
        return Internal\Loader::load();
    }

    /**
     * Look up all addresses for a hostname.
     *
     * @param string $hostname The hostname to look up (case-insensitive).
     *
     * @return list<Address>
     */
    public function lookup(string $hostname): array
    {
        return $this->entries[strtolower($hostname)] ?? [];
    }
}
