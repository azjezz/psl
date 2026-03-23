<?php

declare(strict_types=1);

namespace Psl\DNS\System;

use Psl\DNS\Exception;
use Psl\DNS\Nameserver;

/**
 * Discovered system DNS settings.
 *
 * Contains the nameservers and search domains detected from the
 * operating system's DNS configuration.
 *
 * @api
 */
final readonly class Settings
{
    /**
     * @param list<Nameserver> $nameservers The discovered nameserver entries.
     * @param list<string> $searchDomains The DNS search domains for short name resolution.
     */
    public function __construct(
        public array $nameservers,
        public array $searchDomains = [],
    ) {}

    /**
     * Load the system DNS settings for the current OS.
     *
     * Uses non-blocking process execution to read OS-specific DNS configuration:
     * - Linux/FreeBSD: `cat /etc/resolv.conf`
     * - macOS: `scutil --dns`
     * - Windows: `ipconfig /all`
     *
     * @throws Exception\SystemException If the system configuration cannot be loaded.
     */
    public static function load(): self
    {
        return Internal\Loader::load();
    }
}
