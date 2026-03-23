<?php

declare(strict_types=1);

namespace Psl\DNS\System\Internal;

use Psl\DNS\Nameserver;
use Psl\DNS\System\Settings;

use function array_values;
use function explode;
use function implode;
use function in_array;
use function preg_match;
use function preg_match_all;
use function preg_split;
use function str_contains;
use function trim;

/**
 * Parses macOS `scutil --dns` output.
 *
 * The output contains numbered resolver blocks, each describing a DNS
 * resolver configuration for a specific scope (interface, domain, etc.).
 *
 * Example output:
 *
 *     DNS configuration
 *
 *     resolver #1
 *       search domain[0] : example.com
 *       nameserver[0] : 8.8.8.8
 *       nameserver[1] : 8.8.4.4
 *       if_index : 6 (en0)
 *       flags    : Request A records, Request AAAA records
 *       reach    : 0x00000002 (Reachable)
 *       order    : 200000
 *
 *     resolver #2
 *       domain   : local
 *       options  : mdns
 *       timeout  : 5
 *       flags    : Request A records, Request AAAA records
 *       reach    : 0x00000000 (Not Reachable)
 *       order    : 300000
 *
 *     DNS configuration (for scoped queries)
 *
 *     resolver #1
 *       search domain[0] : example.com
 *       nameserver[0] : 192.168.1.1
 *       if_index : 6 (en0)
 *       flags    : Scoped, Request A records, Request AAAA records
 *       reach    : 0x00000002 (Reachable)
 *       order    : 200000
 *
 * Resolver blocks with `options : mdns` or `domain : local` are multicast
 * DNS resolvers and are skipped since they are not standard unicast DNS.
 *
 * @internal
 */
final class ScutilDnsParser
{
    /**
     * @param string $output Raw output of `scutil --dns`.
     */
    public static function parse(string $output): Settings
    {
        $nameservers = [];
        $searchDomains = [];
        $seenNameservers = [];

        $blocks = preg_split('/(?=resolver\s+#\d+)/i', $output);
        if (false === $blocks) {
            return new Settings($nameservers, $searchDomains);
        }

        foreach ($blocks as $block) {
            if (!preg_match('/^resolver\s+#\d+/i', trim($block))) {
                continue;
            }

            if (self::isMulticastBlock($block)) {
                continue;
            }

            if (self::isScopedBlock($block)) {
                continue;
            }

            $blockNameservers = self::extractNameservers($block);
            $blockDomains = self::extractSearchDomains($block);
            $scopeDomain = self::extractDomain($block);
            $forDomains = [];
            if ($scopeDomain !== null) {
                $forDomains = [$scopeDomain];
            }

            $port = self::extractPort($block);

            foreach ($blockNameservers as $ns) {
                $key = $ns . ':' . $port . '|' . implode(',', $forDomains);
                if (isset($seenNameservers[$key])) {
                    continue;
                }

                $seenNameservers[$key] = true;
                $nameservers[] = new Nameserver($ns, $port, $forDomains);
            }

            foreach ($blockDomains as $domain) {
                if (in_array($domain, $searchDomains, true)) {
                    continue;
                }

                $searchDomains[] = $domain;
            }
        }

        return new Settings($nameservers, $searchDomains);
    }

    /**
     * Check whether a resolver block is for multicast DNS (mDNS).
     */
    private static function isMulticastBlock(string $block): bool
    {
        if (preg_match('/options\s*:\s*mdns/i', $block)) {
            return true;
        }

        if (preg_match('/domain\s*:\s*local\s*$/mi', $block)) {
            return true;
        }

        return false;
    }

    /**
     * Check whether a resolver block is a scoped (interface-specific) resolver.
     */
    private static function isScopedBlock(string $block): bool
    {
        return (bool) preg_match('/flags\s*:.*Scoped/i', $block);
    }

    /**
     * @return list<non-empty-string>
     */
    private static function extractNameservers(string $block): array
    {
        $nameservers = [];
        $matches = null;
        preg_match_all('/nameserver\[\d+\]\s*:\s*(\S+)/i', $block, $matches);

        foreach ($matches[1] ?? [] as $ns) {
            $ns = trim($ns);
            if ($ns !== '') {
                if (str_contains($ns, '%')) {
                    [$ns] = explode('%', $ns, 2);
                    $ns = trim($ns);
                }

                if ($ns !== '') {
                    $nameservers[] = $ns;
                }
            }
        }

        return array_values($nameservers);
    }

    /**
     * @return list<string>
     */
    private static function extractSearchDomains(string $block): array
    {
        $domains = [];
        $matches = null;
        preg_match_all('/search\s+domain\[\d+\]\s*:\s*(\S+)/i', $block, $matches);

        foreach ($matches[1] ?? [] as $domain) {
            $domain = trim($domain);
            if ($domain !== '') {
                $domains[] = $domain;
            }
        }

        return array_values($domains);
    }

    /**
     * @return int<0, 65535>
     */
    private static function extractPort(string $block): int
    {
        $matches = null;
        if (preg_match('/^\s*port\s*:\s*(\d+)\s*$/mi', $block, $matches)) {
            $port = (int) $matches[1];
            if ($port > 0 && $port <= 65_535) {
                return $port;
            }
        }

        return 53;
    }

    /**
     * Extract the domain scope from a resolver block.
     */
    private static function extractDomain(string $block): null|string
    {
        $matches = null;
        if (preg_match('/^\s*domain\s*:\s*(\S+)\s*$/mi', $block, $matches)) {
            $domain = trim($matches[1]);
            if ($domain !== '' && $domain !== 'local') {
                return $domain;
            }
        }

        return null;
    }
}
