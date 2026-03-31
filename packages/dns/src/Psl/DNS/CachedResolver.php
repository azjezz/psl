<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Cache\StoreInterface;
use Psl\DateTime\Duration;
use Psl\DNS\Record\RecordType;
use Psl\Ref;

use function md5;
use function min;
use function serialize;
use function strtolower;

use const PHP_INT_MAX;

/**
 * Caching decorator for any DNS resolver.
 *
 * Wraps an existing {@see ResolverInterface} and caches responses using a
 * {@see StoreInterface}. Cache TTL is derived from the minimum record TTL
 * in the response.
 *
 * @api
 */
final readonly class CachedResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * @param ResolverInterface $inner The underlying resolver to cache results from.
     * @param StoreInterface $cache The cache store for DNS responses.
     */
    public function __construct(
        private ResolverInterface $inner,
        private StoreInterface $cache,
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
        $cacheKey = self::buildKey($name, $type, $ednsOptions);

        /** @var Ref<Duration|null> $ttl */
        $ttl = new Ref(null);

        $response = $this->cache->compute(
            $cacheKey,
            function () use ($name, $type, $cancellation, $ednsOptions, $ttl): Response {
                $response = $this->inner->query($name, $type, $cancellation, $ednsOptions);

                if ($response->code === ResponseCode::NoError || $response->code === ResponseCode::NonExistentDomain) {
                    $minTtl = self::extractMinTtl($response);
                    if ($minTtl > 0) {
                        $ttl->value = Duration::seconds($minTtl);
                    }
                }

                return $response;
            },
            Duration::hours(24),
        );

        if ($ttl->value !== null) {
            $this->cache->update($cacheKey, static fn(): Response => $response, $ttl->value);
        }

        return $response;
    }

    /**
     * @param list<EDNS\OptionInterface> $ednsOptions
     *
     * @return non-empty-string
     */
    private static function buildKey(string $name, RecordType $kind, array $ednsOptions): string
    {
        $key = strtolower($name) . ':' . $kind->value;
        if ($ednsOptions !== []) {
            $key .= ':' . md5(serialize($ednsOptions));
        }

        return $key;
    }

    /**
     * Extract the minimum TTL across answer and authority records.
     */
    private static function extractMinTtl(Response $response): int
    {
        $minTtl = PHP_INT_MAX;
        $hasRecords = false;

        foreach ($response->answers as $record) {
            $hasRecords = true;
            $minTtl = min($minTtl, (int) $record->duration->getTotalSeconds());
        }

        if (!$hasRecords) {
            foreach ($response->authority as $record) {
                $hasRecords = true;
                $minTtl = min($minTtl, (int) $record->duration->getTotalSeconds());
            }
        }

        return $hasRecords ? $minTtl : 60;
    }
}
