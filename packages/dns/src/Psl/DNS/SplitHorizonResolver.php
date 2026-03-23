<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Record\RecordType;

use function rtrim;
use function str_ends_with;
use function strtolower;

/**
 * Routes DNS queries to different resolvers based on the query domain name.
 *
 * Implements split-horizon DNS: scoped domains are resolved by their
 * designated resolver, everything else falls through to the default.
 * Reverse lookups (.arpa) always use the default resolver.
 *
 * Domain matching is suffix-based on domain boundaries: a route for
 * "corp.internal" matches "db.corp.internal" and "corp.internal" itself,
 * but not "notcorp.internal".
 *
 * @api
 */
final readonly class SplitHorizonResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * @param list<Route> $routes Domain patterns mapped to resolvers.
     * @param ResolverInterface $default Resolver for unmatched domains and reverse lookups.
     */
    public function __construct(
        private array $routes,
        private ResolverInterface $default,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        $resolver = $this->resolve($name);

        return $resolver->query($name, $type, $cancellation, $ednsOptions);
    }

    /**
     * Find the resolver for the given query name by matching against configured routes.
     */
    private function resolve(string $name): ResolverInterface
    {
        $normalized = strtolower(rtrim($name, '.'));

        foreach ($this->routes as $route) {
            foreach ($route->domains as $domain) {
                if (self::matchesDomain($normalized, strtolower($domain))) {
                    return $route->resolver;
                }
            }
        }

        return $this->default;
    }

    /**
     * Check whether a name matches a domain pattern on a domain boundary.
     */
    private static function matchesDomain(string $name, string $pattern): bool
    {
        if ($name === $pattern) {
            return true;
        }

        return str_ends_with($name, '.' . $pattern);
    }
}
