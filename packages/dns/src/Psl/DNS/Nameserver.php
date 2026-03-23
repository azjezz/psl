<?php

declare(strict_types=1);

namespace Psl\DNS;

/**
 * A single nameserver with host, port, and optional search domain scope.
 *
 * When $forDomains is non-empty, this nameserver should only be used for
 * queries matching those domains (split DNS / per-domain resolvers).
 *
 * @api
 */
final readonly class Nameserver
{
    /**
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     * @param list<string> $forDomains Domains this nameserver is scoped to (empty = all domains).
     */
    public function __construct(
        public string $host,
        public int $port = 53,
        public array $forDomains = [],
    ) {}
}
