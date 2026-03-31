<?php

declare(strict_types=1);

namespace Psl\DNSSEC;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Cache\StoreInterface;
use Psl\DateTime\Duration;
use Psl\Ref;

use function min;
use function strtolower;

use const PHP_INT_MAX;

/**
 * Caching decorator for any trust chain resolver.
 *
 * Wraps an existing {@see TrustChainResolverInterface} and caches results
 * using a {@see StoreInterface}. Cache TTLs are determined by the result status:
 * secure results use the minimum DNSKEY record TTL, insecure results are cached
 * for 5 minutes, and bogus results are cached for 30 seconds.
 *
 * @api
 */
final readonly class CachedTrustChainResolver implements TrustChainResolverInterface
{
    /**
     * Cache TTL in seconds for insecure (provably unsigned) results.
     */
    private const int INSECURE_CACHE_TTL = 300;

    /**
     * Cache TTL in seconds for bogus (validation failed) results.
     */
    private const int BOGUS_CACHE_TTL = 30;

    /**
     * @param TrustChainResolverInterface $inner The underlying resolver to cache results from.
     * @param StoreInterface $cache The cache store for persisting trust chain results.
     */
    public function __construct(
        private TrustChainResolverInterface $inner,
        private StoreInterface $cache,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function resolve(
        string $zone,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): TrustChainResult {
        $cacheKey = 'dnssec:' . strtolower($zone);

        /** @var Ref<Duration|null> $ttl */
        $ttl = new Ref(null);

        $result = $this->cache->compute(
            $cacheKey,
            function () use ($zone, $cancellation, $ttl): TrustChainResult {
                $result = $this->inner->resolve($zone, $cancellation);

                $ttl->value = match ($result->status) {
                    TrustChainStatus::Insecure => Duration::seconds(self::INSECURE_CACHE_TTL),
                    TrustChainStatus::Bogus => Duration::seconds(self::BOGUS_CACHE_TTL),
                    default => self::extractKeysTtl($result),
                };

                return $result;
            },
            Duration::hours(24),
        );

        if ($ttl->value !== null) {
            $this->cache->update($cacheKey, static fn(): TrustChainResult => $result, $ttl->value);
        }

        return $result;
    }

    /**
     * Extract the minimum TTL from the validated DNSKEY records in the result.
     *
     * Returns null if the result contains no keys or if the minimum TTL is zero or negative.
     */
    private static function extractKeysTtl(TrustChainResult $result): null|Duration
    {
        $minTtl = PHP_INT_MAX;
        $hasKeys = false;

        foreach ($result->keys as $key) {
            $hasKeys = true;
            $minTtl = min($minTtl, (int) $key->duration->getTotalSeconds());
        }

        if (!$hasKeys || $minTtl <= 0) {
            return null;
        }

        return Duration::seconds($minTtl);
    }
}
