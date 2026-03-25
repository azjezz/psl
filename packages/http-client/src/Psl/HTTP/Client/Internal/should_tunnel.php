<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use function ltrim;
use function str_ends_with;

/**
 * Determine whether a host should be tunneled or bypassed.
 *
 * Checks the host against a list of no-tunneling rules. Rules support:
 * - Wildcard: "*" matches all hosts (bypass everything).
 * - Exact match: "localhost" matches exactly "localhost".
 * - Domain suffix: ".example.com" or "example.com" matches any subdomain
 *   (e.g., "api.example.com", "sub.deep.example.com").
 *
 * @param non-empty-string $host The hostname to check.
 * @param list<non-empty-string> $noTunneling The no-tunneling rules.
 *
 * @return bool True if the host should be tunneled, false if it should bypass.
 *
 * @internal
 */
function should_tunnel(string $host, array $noTunneling): bool
{
    if ($noTunneling === []) {
        return true;
    }

    foreach ($noTunneling as $rule) {
        if ($rule === '*') {
            return false;
        }

        if ($host === $rule) {
            return false;
        }

        $dotRule = '.' . ltrim($rule, '.');
        if (str_ends_with($host, $dotRule)) {
            return false;
        }
    }

    return true;
}
